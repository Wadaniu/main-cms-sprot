<?php
/**
 * @copyright Copyright (c) 2021 勾股工作室
 * @license https://opensource.org/licenses/Apache-2.0
 * @link https://www.gougucms.com
 */
namespace app\admin\model;
use think\model;
class FootballPlayer extends Model
{
    protected $connection = 'compDataDb';

    /**
    * 获取分页列表
    * @param $where
    * @param $param
    */
    public function getFootballPlayerList($where, $param)
    {
		$rows = empty($param['limit']) ? get_config('app . page_size') : $param['limit'];
		$order = empty($param['order']) ? 'id desc' : $param['order'];
        $list = self::where($where)->field('id,name_zh,name_zht,name_en,short_name_zh,short_name_zht,short_name_en,logo,country_id,nationality,national_logo,birthday,age,height,weight,market_value,market_value_currency,contract_until,preferred_foot,position,positions')->order($order)->paginate($rows, false, ['query' => $param]);
		return $list;
    }

    /**
    * 添加数据
    * @param $param
    */
    public function addFootballPlayer($param)
    {
		$insertId = 0;
        try {
			$param['create_time'] = time();
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
    public function editFootballPlayer($param)
    {
        try {
            $param['update_time'] = time();
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
    public function getFootballPlayerById($id)
    {
        $info = self::where('id', $id)->find();
		return $info;
    }

    /**
    * 删除信息
    * @param $id
    * @return array
    */
    public function delFootballPlayerById($id,$type=0)
    {
		if($type==0){
			//逻辑删除
			try {
				$param['delete_time'] = time();
				self::where('id', $id)->update(['delete_time'=>time()]);
				add_log('delete', $id);
			} catch(\Exception $e) {
				return to_assign(1, '操作失败，原因：'.$e->getMessage());
			}
		}
		else{
			//物理删除
			try {
				self::where('id', $id)->delete();
				add_log('delete', $id);
			} catch(\Exception $e) {
				return to_assign(1, '操作失败，原因：'.$e->getMessage());
			}
		}
		return to_assign();
    }


    public function sync(){

        try {
            $this->autoSync(false);
        } catch(\Exception $e) {
            return to_assign(1, '操作失败，原因：'.$e->getMessage());
        }
        return to_assign(0,'已成功同步');
    }

    //自动获取数据同步
    public function autoSync(){
        $url = "/api/v5/football/player/list";
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

                $ids[] = $vo["id"];

                if(!isset($vo["ability"])){
                    $vo["ability"] = array();
                }
                if(!isset($vo["characteristics"])){
                    $vo["characteristics"] = array();
                }
                if(!isset($vo["positions"])){
                    $vo["positions"] = array();
                }

                $vo["ability"] = json_encode($vo["ability"]);
                $vo["characteristics"] = json_encode($vo["characteristics"]);
                $vo["positions"] = json_encode($vo["positions"]);

                $param[] = [
                    "id"=>$vo["id"],
                    "name_zh"=>$vo["name_zh"],
                    "name_zht"=>$vo["name_zht"],
                    "name_en"=>$vo["name_en"],
                    "short_name_zh"=>$vo["short_name_zh"],
                    "short_name_zht"=>$vo["short_name_zht"],
                    "short_name_en"=>$vo["short_name_en"],
                    "logo"=>$vo["logo"],
                    "country_id"=>$vo["country_id"],
                    "nationality"=>$vo["nationality"],
                    "national_logo"=>$vo["national_logo"],
                    "birthday"=>$vo["birthday"],
                    "age"=>$vo["age"],
                    "height"=>$vo["height"],
                    "weight"=>$vo["weight"],
                    "market_value"=>$vo["market_value"],
                    "market_value_currency"=>$vo["market_value_currency"],
                    "contract_until"=>$vo["contract_until"],
                    "preferred_foot"=>$vo["preferred_foot"],
                    "ability"=>$vo["ability"],
                    "characteristics"=>$vo["characteristics"],
                    "position"=>$vo["position"],
                    "positions"=>$vo["positions"],
                    "updated_at"=>$vo["updated_at"],
                ];

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
            //print_r($param);exit;
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

    }
}

