<?php
/**
 * @copyright Copyright (c) 2021 勾股工作室
 * @license https://opensource.org/licenses/Apache-2.0
 * @link https://www.gougucms.com
 */
namespace app\commonModel;
use think\facade\Cache;
use think\facade\Db;
use think\facade\Env;
use think\model;

class FootballTeam extends Model
{
    protected $connection = 'compDataDb';
    public static $HOT_DATA =  "FootballTeamHotData";
    public static $CACHE_SHORT_NAME_ZH =  "FootballTeamShortNameZh";
    public $teamInfo = [];
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
                $sortConf = Db::name('hot_team_sort')->where('type',0)->where('team_id',$item->id)->findOrEmpty();

                $item->sort = $sortConf['sort'] ?? 0;
                $item->status = $sortConf['is_hot'] ?? 0;
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
            $sortConf = Db::name('hot_team_sort')->where('team_id',$param['id'])->where('type',0)->find();

            $sort = [
                'team_id'   =>  $param['id'],
                'sort'      =>  $param['sort'],
                'is_hot'    =>  $param['status'],
                'type'      =>  0
            ];
            if ($sortConf){
                Db::name('hot_team_sort')->update($sort);
            }else{
                Db::name('hot_team_sort')->insert($sort);
            }

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
        //获取项目排序字段
        $sortConf = Db::name('hot_team_sort')->where('type',0)->where('team_id',$id)->find();

        $info->sort = $sortConf['sort'] ?? 0;
        $info->status = $sortConf['is_hot'] ?? 0;
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

    public function getList($where, $param)
    {
        $rows = empty($param['limit']) ? get_config('app . page_size') : $param['limit'];
        $order = empty($param['order']) ? 'id desc' : $param['order'];
        $list = self::where('logo','<>','')->where($where)->field('id,short_name_zh,logo')
            ->order($order)
            ->paginate($rows, false, ['query' => $param])
            ->each(function ($item, $key) {
                $item->sphere_type="zuqiu";
            });
        return $list;
    }




    /**
     * 根据球队名称查找球队ID
     * 同时为了相同的球队名称重复查做了优化
     * */
    public function getTeamInfoByName($name,$column){
        $id = array_search($name,$this->teamInfo);
        if($id){
            return $id;
        }
        $teamInfo = self::where($column,$name)->find();
        if($teamInfo){
            $this->teamInfo[$teamInfo->id] = $teamInfo->name_zh;
            return $teamInfo->id;
        }
        return 0;
    }

    public function getHotData($limit = 0,$compId = 0){
        $key = self::$HOT_DATA;
        $data = Cache::store('common_redis')->get($key);
        if(!empty($data)){

            $res = $this->filterByCompId($data,$compId);
            if ($limit > 0){
                $res = array_slice($res,0,$limit);
            }
            return $res;
        }
        $sort = Db::name('hot_team_sort')->where('is_hot',1)->where('type',0)->column('*','team_id');

        $ids = array_keys($sort);
        $data = self::where('id','IN',$ids)->field("id,name_zh,short_name_zh,logo,competition_id")->select()->toArray();
        foreach ($data as &$item){
            $item['sort'] = $sort[$item['id']]['sort'];
            $item['sphere_type'] = 'zuqiu';
        }
        array_multisort(array_column($data,'sort'),SORT_DESC,$data);
        Cache::store('common_redis')->set($key,$data);

        //过滤联赛下队伍
        $res = $this->filterByCompId($data,$compId);
        if ($limit > 0){
            $res = array_slice($res,0,$limit);
        }

        return $data;
    }

    public function filterByCompId($data,$compId): array
    {
        $res = [];
        if ($compId > 0){
            foreach ($data as $key=>$item){
                if ($item['competition_id'] == $compId){
                    $res[$key] = $item;
                }
            }
        }else{
            $res = $data;
        }

        return $res;
    }
}

