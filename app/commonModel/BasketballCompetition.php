<?php
/**
 * @copyright Copyright (c) 2021 勾股工作室
 * @license https://opensource.org/licenses/Apache-2.0
 * @link https://www.gougucms.com
 */
namespace app\commonModel;
use think\facade\Cache;
use think\model;

class BasketballCompetition extends Model
{
    protected $connection = 'compDataDb';

    public static $HOT_DATA =  "BasketballCompetitionHotData";
    public static $CACHE_SHORT_NAME_ZH =  "BasketballCompetitionShortNameZh";
    /**
    * 获取分页列表
    * @param $where
    * @param $param
    */
    public function getBasketballCompetitionList($where, $param)
    {
		$rows = empty($param['limit']) ? get_config('app . page_size') : $param['limit'];
		$order = empty($param['order']) ? 'status desc,sort asc,id desc' : $param['order'];
        $list = self::where($where)
            ->field('id,category_id,country_id,name_zh,name_zht,name_en,short_name_zh,short_name_zht,short_name_en,logo,updated_at,sort,status')
            ->order($order)
            ->paginate($rows, false, ['query' => $param])
            ->each(function ($item, $key) {
                $item->updated_at = date("Y-m-d H:i:s",$item->updated_at);
                if($item->status==1){
                    $item->status = "是";
                }else{
                    $item->status = "否";
                }
            });

		return $list;
    }




    /**
    * 添加数据
    * @param $param
    */
    public function addBasketballCompetition($param)
    {
		$insertId = 0;
        try {
			$param['updated_at'] = time();
			$insertId = self::strict(false)->field(true)->insertGetId($param);
			add_log('add', $insertId, $param);
        } catch(\Exception $e) {
			return to_assign(1, '操作失败，原因：'.$e->getMessage());
        }
        Cache::delete(self::$HOT_DATA);
        Cache::delete(self::$CACHE_SHORT_NAME_ZH);
		return to_assign(0,'操作成功',['aid'=>$insertId]);
    }

    /**
    * 编辑信息
    * @param $param
    */
    public function editBasketballCompetition($param)
    {
        try {
            $param['updated_at'] = time();
            self::where('id', $param['id'])->strict(false)->field(true)->update($param);
			add_log('edit', $param['id'], $param);
        } catch(\Exception $e) {
			return to_assign(1, '操作失败，原因：'.$e->getMessage());
        }
        Cache::delete(self::$HOT_DATA);
        Cache::delete(self::$CACHE_SHORT_NAME_ZH);
		return to_assign();
    }
	

    /**
    * 根据id获取信息
    * @param $id
    */
    public function getBasketballCompetitionById($id,$field="*")
    {
        $info = self::where('id', $id)->field($field)->find();
		return $info;
    }

    /**
    * 删除信息
    * @param $id
    * @return array
    */
    public function delBasketballCompetitionById($id,$type=0)
    {

			//物理删除
			try {
				self::where('id', $id)->delete();
				add_log('delete', $id);
			} catch(\Exception $e) {
				return to_assign(1, '操作失败，原因：'.$e->getMessage());
			}
        Cache::delete(self::$HOT_DATA);
        Cache::delete(self::$CACHE_SHORT_NAME_ZH);
		return to_assign();
    }

    /**
     * 获取热点数据
     */
    public function getHotData(){
        $key = self::$HOT_DATA;
        $data = Cache::get($key);
        if(!empty($data)){
            return $data;
        }
        $info = self::where(["status"=>1])->field("id,name_zh,short_name_zh,logo")->order("sort asc,id desc")->select();
        $data = $info->toArray();
        Cache::set($key,$data);
        return $data;
    }

    /**
     * 中文简称
     */
    public function getShortNameZh($id){
        $key = self::$CACHE_SHORT_NAME_ZH;
        $data = Cache::get($key);
        if(empty($data)){
            $data = self::where([])->field("id,short_name_zh")->column("short_name_zh","id");
            Cache::set($key,$data);
        }
        if(isset($data[$id])){
            return $data[$id];
        }
        return "";
    }


    public function autoSync(){
        $url = "/api/v5/basketball/competition/list";
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
                $vo["sort"] = 50;
                $vo["status"] = 0;
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
                    unset($vo["status"]);
                    unset($vo["sort"]);
                    self::where('id', $vo['id'])->strict(false)->field(true)->update($vo);
                    unset($param[$key]);
                }
            }
            if(!empty($param)){

                    self::strict(false)->field(true)->insertAll($param);

            }
            Cache::delete(self::$HOT_DATA);
            Cache::delete(self::$CACHE_SHORT_NAME_ZH);
            if($getApiInfo["query"]['total'] == 1 || $getApiInfo["query"]['total'] == 0){
                array_multisort(array_column($getApiInfo["results"],'updated_at'),$getApiInfo["results"]);
                $end = end($getApiInfo["results"]);
                $getApiInfo['query']['max_time'] = $end['updated_at'];
            }
            setApiCacheData($url,$getApiInfo["query"]);
        }
        return $getApiInfo;
    }

    public function sync(){
        $this->autoSync(false);
        return to_assign(0,'已成功同步');
    }


}

