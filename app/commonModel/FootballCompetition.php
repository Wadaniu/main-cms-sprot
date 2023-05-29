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

class FootballCompetition extends Model
{
    protected $connection = 'compDataDb';

    public static $HOT_DATA =  "FootballCompetitionHotData";
    public static $CACHE_SHORT_NAME_ZH =  "FootballCompetitionShortNameZh";
    public static $COMPETITION_TYPE = [
        0=>"未知",
        1=>"联赛",
        2=>"杯赛",
        3=>"友谊赛",
    ];

    public static function getByPY(string $compName)
    {
        return self::where('short_name_py',$compName)->findOrEmpty();
    }


    /**
    * 获取分页列表
    * @param $where
    * @param $param
    */
    public function getFootballCompetitionList($where, $param)
    {

        $competitionType = self::$COMPETITION_TYPE;
		$rows = empty($param['limit']) ? get_config('app . page_size') : $param['limit'];
		$order = empty($param['order']) ? 'status desc,sort asc,id desc' : $param['order'];
        $list = self::where($where)->field('id,category_id,country_id,name_zh,name_zht,name_en,short_name_zh,short_name_zht,short_name_en,logo,type,title_holder,most_titles,newcomers,divisions,host,primary_color,secondary_color,status,updated_at')
            ->order($order)
            ->paginate($rows, false, ['query' => $param])
            ->each(function ($item, $key) {
                if(isset($competitionType[$item->type])){
                    $item->type = $competitionType[$item->type];
                }
                $item->updated_at = date("Y-m-d H:i:s",$item->updated_at);
                if($item->status==1){
                    $item->status = "是";
                }else{
                    $item->status = "否";
                }
            });;
		return $list;
    }

    /**
     * 获取分页列表
     * @param $where
     * @param $param
     */
    public function getList($where, $param)
    {
        $rows = empty($param['limit']) ? get_config('app . page_size') : $param['limit'];
        $order = empty($param['order']) ? 'status desc,sort asc,id desc' : $param['order'];
        $list = self::where($where)->field('id,type,short_name_zh,short_name_py,logo,status,sort')
            ->order($order)
            ->paginate($rows, false, ['query' => $param])
            ->each(function ($item, $key) {
                $item->sphere_type="zuqiu";
            });
        return $list;
    }

    /**
    * 添加数据
    * @param $param
    */
    public function addFootballCompetition($param)
    {
		$insertId = 0;
        try {
			$param['create_time'] = time();
			$insertId = self::strict(false)->field(true)->insertGetId($param);
			add_log('add', $insertId, $param);
        } catch(\Exception $e) {
			return to_assign(1, '操作失败，原因：'.$e->getMessage());
        }
        Cache::delete(self::$HOT_DATA.Env::get('HOME.HOME_SPACE'));
        Cache::delete(self::$CACHE_SHORT_NAME_ZH);
		return to_assign(0,'操作成功',['aid'=>$insertId]);
    }

    /**
    * 编辑信息
    * @param $param
    */
    public function editFootballCompetition($param)
    {
        try {
            $param['updated_at'] = time();
            self::where('id', $param['id'])->strict(false)->field(true)->update($param);
            $sortConf = Db::name('comp_sort')->where('comp_id',$param['id'])->where('type',0)->find();

            $sort = [
                'comp_id'    =>  $param['id'],
                'sort'  =>  $param['sort'],
                'is_hot'=>  $param['status'],
                'type'=>  0
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
        Cache::delete(self::$HOT_DATA.Env::get('HOME.HOME_SPACE'));
        Cache::delete(self::$CACHE_SHORT_NAME_ZH);
		return to_assign();
    }
	

    /**
    * 根据id获取信息
    * @param $id
    */
    public function getFootballCompetitionById($id)
    {
        $info = self::where('id', $id)->find();
        //获取项目排序字段
        $sortConf = Db::name('comp_sort')->where('type',0)->where('comp_id',$id)->find();

        $info->sort = $sortConf['sort'] ?? 0;
        $info->status = $sortConf['is_hot'] ?? 0;
		return $info;
    }

    /**
    * 删除信息
    * @param $id
    * @return array
    */
    public function delFootballCompetitionById($id,$type=0)
    {

			//物理删除
			try {
				self::where('id', $id)->delete();
				add_log('delete', $id);
			} catch(\Exception $e) {
				return to_assign(1, '操作失败，原因：'.$e->getMessage());
			}
        Cache::delete(self::$HOT_DATA.Env::get('HOME.HOME_SPACE'));
        Cache::delete(self::$CACHE_SHORT_NAME_ZH);
        return to_assign();
    }
    /**
     * 获取热点数据
     */
    public function getHotData(){
        $key = self::$HOT_DATA.Env::get('HOME.HOME_SPACE');
        $data = Cache::get($key);
        if(!empty($data)){
            return $data;
        }
        $sort = Db::name('comp_sort')->where('is_hot',1)->where('type',0)->column('*','comp_id');

        $ids = array_keys($sort);
        $data = self::where('id','IN',$ids)->field("id,name_zh,short_name_py,short_name_zh,logo")->select()->toArray();
        foreach ($data as &$item){
            $item['sort'] = $sort[$item['id']]['sort'];
        }
        array_multisort(array_column($data,'sort'),SORT_DESC,$data);
        Cache::set($key,$data);
        return $data;
    }
    /**
     * 中文简称
     */
    public function getShortNameZh($id){
        $key = self::$CACHE_SHORT_NAME_ZH;
        $data = Cache::store('common_redis')->get($key);
        if(empty($data)){
            $data = self::field("id,short_name_zh,short_name_py")->select()->toArray();
            $data = array_column($data,null,'id');
            Cache::store('common_redis')->set($key,$data);
        }
        if(isset($data[$id])){
            return $data[$id];
        }
        return "";
    }

    public function syncTest($isCache = true){
        $url = "/api/v5/football/competition/table/detail";
        $apiDataInfo = getApiDataInfo($url,$isCache);
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
        var_dump($getApiInfo);die;
    }
    public function autoSync(){
        $url = "/api/v5/football/competition/list";
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
                $vo["sort"] = 0;
                $vo["status"] = 0;
                $vo["title_holder"] = json_encode($vo["title_holder"]);
                $vo["most_titles"] = json_encode($vo["most_titles"]);
                $vo["newcomers"] = json_encode($vo["newcomers"]);
                $vo["divisions"] = json_encode($vo["divisions"]);
                $vo["host"] = json_encode($vo["host"]);

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
                try {
                    self::strict(false)->field(true)->insertAll($param);

                } catch(\Exception $e) {
                    return to_assign(1, '操作失败，原因：'.$e->getMessage());
                }
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

    public function info($id)
    {
        if ($id <= 0){
            return [];
        }
        $info = self::field('id,name_zh,short_name_zh,logo,intro,primary_color,secondary_color')->find($id);

        return $info;
    }

}




