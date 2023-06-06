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

class BasketballCompetition extends Model
{
    protected $connection = 'compDataDb';

    public static $HOT_DATA =  "BasketballCompetitionHotData";
    public static $CACHE_SHORT_NAME_ZH =  "BasketballCompetitionShortNameZh";

    public static function getByPY(string $compName)
    {
        return self::where('short_name_py',$compName)->findOrEmpty();
    }
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
                $sortConf = Db::name('comp_sort')->where('type',1)->where('comp_id',$item->id)->findOrEmpty();

                $item->sort = $sortConf['sort'] ?? 0;
                $item->status = $sortConf['is_hot'] ?? 0;
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
            $sortConf = Db::name('comp_sort')->where('comp_id',$param['id'])->where('type',1)->find();
            $sort = [
                'comp_id'    =>  $param['id'],
                'sort'  =>  $param['sort'],
                'is_hot'=>  $param['status'],
                'type'=>  1
            ];
            if ($sortConf){
                Db::name('comp_sort')->update($sort);
            }else{
                Db::name('comp_sort')->insert($sort);
            }
            add_log('edit', $param['id'], $param);
        } catch(\Exception $e) {
            return to_assign(1, '操作失败，原因：'.$e->getMessage());
        }
        Cache::store('common_redis')->delete(self::$HOT_DATA);
        Cache::store('common_redis')->delete(self::$CACHE_SHORT_NAME_ZH);
        return to_assign();
    }
	

    /**
    * 根据id获取信息
    * @param $id
    */
    public function getBasketballCompetitionById($id,$field="*")
    {
        $info = self::where('id', $id)->field($field)->find();
        //获取项目排序字段
        $sortConf = Db::name('comp_sort')->where('type',1)->where('comp_id',$id)->find();

        $info->sort = $sortConf['sort'] ?? 0;
        $info->status = $sortConf['is_hot'] ?? 0;

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
    public function getHotData($limit = 0){
        $key = self::$HOT_DATA;
        $data = Cache::store('common_redis')->get($key);
        if(!empty($data)){
            if ($limit > 0){
                $data = array_slice($data,0,$limit);
            }
            return $data;
        }
        $sort = Db::name('comp_sort')->where('is_hot',1)->where('type',1)->column('*','comp_id');
        $ids = array_keys($sort);
        $data = self::where('id','IN',$ids)->field("id,name_zh,short_name_zh,short_name_py,logo")->select()->toArray();
        foreach ($data as &$item){
            $item['sort'] = $sort[$item['id']]['sort'];
            $item['sphere_type'] = 'lanqiu';
        }
        array_multisort(array_column($data,'sort'),SORT_DESC,$data);
        Cache::store('common_redis')->set($key,$data);

        if ($limit > 0){
            $data = array_slice($data,0,$limit);
        }
        return $data;
    }

    /**
     * 中文简称
     */
    public function getShortNameZh($id){
        $key = self::$CACHE_SHORT_NAME_ZH;
        $data = Cache::store('common_redis')->get($key);
        if(empty($data)){
            $data = self::field("id,short_name_zh,short_name_py,name_zh")->select()->toArray();
            $data = array_column($data,null,'id');
            Cache::store('common_redis')->set($key,$data);
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

    /**
     * 获取分页列表
     * @param $where
     * @param $param
     */
    public function getList($keyword, $param)
    {
        $rows = empty($param['limit']) ? get_config('app . page_size') : $param['limit'];
        $page = ($param['page'] - 1) > 0? $param['page'] - 1: 0;
        $order = empty($param['order']) ? 'id desc' : $param['order'];
        $query = self::where('logo','<>','')->field('id,short_name_zh,name_zh,short_name_py,logo');
        if (!empty($keyword)){
            $query->whereRaw("name_zh like :word OR short_name_zh = :key",['word'=>'%'.$keyword.'%','key'=>$keyword]);
        }
        $count = $query->count();
        $list = $query->limit($page,$rows)->order($order)->select()->toArray();

        foreach ($list as &$item){
            $item['sphere_type'] = 'lanqiu';
        }

        $res = [
            'total' => $count,
            'data'  => $list
        ];
        return $res;
    }


}

