<?php
/**
 * @copyright Copyright (c) 2021 勾股工作室
 * @license https://opensource.org/licenses/Apache-2.0
 * @link https://www.gougucms.com
 */
namespace app\commonModel;
use think\facade\Cache;
use think\model;

class FootballTeam extends Model
{
    protected $connection = 'compDataDb';

    public static $CACHE_SHORT_NAME_ZH =  "FootballTeamShortNameZh";
    /**
    * 获取分页列表
    * @param $where
    * @param $param
    */
    public function getFootballTeamList($where, $param)
    {
		$rows = empty($param['limit']) ? get_config('app . page_size') : $param['limit'];
		$order = empty($param['order']) ? 'id desc' : $param['order'];
        $list = self::where($where)->field('id,competition_id,country_id,name_zh,name_zht,name_en,short_name_zh,short_name_zht,short_name_en,logo,national,country_logo,foundation_time,website,venue_id,market_value,market_value_currency,total_players,foreign_players,national_players,updated_at')
            ->order($order)
            ->paginate($rows, false, ['query' => $param])
            ->each(function ($item, $key) {
                $item->updated_at = date("Y-m-d H:i:s",$item->updated_at);
                $item->national = $item->national?"是":"否";
            });
		return $list;
    }

    /**
    * 添加数据
    * @param $param
    */
    public function addFootballTeam($param)
    {
		$insertId = 0;
        try {
            if(empty($param["updated_at"])){
                $param['updated_at'] = time();
            }else{
                $param['updated_at'] = strtotime($param['updated_at']);
            }

			$insertId = self::strict(false)->field(true)->insertGetId($param);
			add_log('add', $insertId, $param);
        } catch(\Exception $e) {
			return to_assign(1, '操作失败，原因：'.$e->getMessage());
        }
		return to_assign(0,'操作成功',['aid'=>$insertId]);
    }

    /**
    * 编辑信息
    * @param $param
    */
    public function editFootballTeam($param)
    {
        try {
            if(empty($param["updated_at"])){
                $param['updated_at'] = time();
            }else{
                $param['updated_at'] = strtotime($param['updated_at']);
            }
            self::where('id', $param['id'])->strict(false)->field(true)->update($param);
			add_log('edit', $param['id'], $param);
        } catch(\Exception $e) {
			return to_assign(1, '操作失败，原因：'.$e->getMessage());
        }
		return to_assign();
    }
	

    /**
    * 根据id获取信息
    * @param $id
    */
    public function getFootballTeamById($id)
    {
        $info = self::where('id', $id)->find();
		return $info;
    }

    /**
    * 删除信息
    * @param $id
    * @return array
    */
    public function delFootballTeamById($id,$type=0)
    {

			//物理删除
			try {
				self::where('id', $id)->delete();
				add_log('delete', $id);
			} catch(\Exception $e) {
				return to_assign(1, '操作失败，原因：'.$e->getMessage());
			}

    }

    /**
     * 中文简称
     */
    public function getShortNameZhLogo($id){

        $limit = 3000;
        $k = intval($id/3000);
        $key = self::$CACHE_SHORT_NAME_ZH.$k;
        $data = Cache::get($key);
        if(empty($data)){
            $start = $k * $limit;
            $end = $start + $limit;
            $where[] = ['id','between',[$start,$end]];
            $voList = self::where($where)->field("id,name_zh,short_name_zh,logo")->select();
            foreach ($voList as $item){
                if(empty($item->short_name_zh)){
                    $item->short_name_zh = $item->name_zh;
                }
                $data[$item->id] = [
                    "short_name_zh"=>  $item->short_name_zh,
                    "logo"=>  $item->logo,
                ];
            }
            if(!empty($data)){
                Cache::set($key,$data,86400);
            }
        }

        if(isset($data[$id])){
            return $data[$id];
        }
        return "";
    }

    public function sync(){
        $getApiInfo = $this->autoSync(false);
        if($getApiInfo["code"]==0){
            if($getApiInfo["query"]["total"]==0){
                return to_assign(0,"已同步最新数据");
            }
            return to_assign(0,"同步成功");
        }
        return to_assign(0,"同步失败");
    }


    public function autoSync(){
        $url = "/api/v5/football/team/list";
        $apiDataInfo = getApiDataInfo($url);
        if(empty($apiDataInfo)){
            return;
        }
        $params = array();
        if($apiDataInfo["update_field"]=="id"){
            $params["id"] = $apiDataInfo["max_id"];
            $params["limit"] = 500;
        }else{
            $params["time"] = $apiDataInfo["max_time"];
            $params["limit"] = 100;
        }

        $getApiInfo = getApiInfo($url,$params);

        if($getApiInfo["code"]==0){

            $param = [];
            $ids = [];
            foreach ($getApiInfo["results"] as $vo){
                $param[] = $vo;
                $ids[] = $vo["id"];
            }
            $voList = self::where("id","in",$ids)->field("id")->select();
            $ids = [];
            foreach ($voList as $v){
                $ids[]=$v->id;
            }

            foreach ($param as $key=>$vo){
                if(in_array($vo["id"],$ids)){
                    self::where('id', $vo['id'])->strict(false)->field(true)->update($vo);
                    unset($param[$key]);
                }
            }
            if(!empty($param)){
                self::strict(false)->field(true)->insertAll($param);
            }
            if($getApiInfo["query"]['total'] == 1 || $getApiInfo["query"]['total'] == 0){
                array_multisort(array_column($getApiInfo["results"],'updated_at'),$getApiInfo["results"]);
                $end = end($getApiInfo["results"]);
                $getApiInfo['query']['max_time'] = $end['updated_at'];
            }
            setApiCacheData($url,$getApiInfo["query"]);
        }
        return $getApiInfo;
    }


}

