<?php
/**
 * @copyright Copyright (c) 2021 勾股工作室
 * @license https://opensource.org/licenses/Apache-2.0
 * @link https://www.gougucms.com
 */
namespace app\commonModel;
use think\model;

class FootballSeason extends Model
{
    protected $connection = 'compDataDb';

    /**
    * 获取分页列表
    * @param $where
    * @param $param
    */
    public function getFootballSeasonList($where, $param)
    {
		$rows = empty($param['limit']) ? get_config('app . page_size') : $param['limit'];
		$order = empty($param['order']) ? 'id desc' : $param['order'];
        $list = self::where($where)->field('id,competition_id,year,is_current,has_player_stats,has_team_stats,has_table,start_time,end_time,updated_at')->order($order)->paginate($rows, false, ['query' => $param]);
		return $list;
    }

    /**
    * 添加数据
    * @param $param
    */
    public function addFootballSeason($param)
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
    public function editFootballSeason($param)
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
    public function getFootballSeasonById($id)
    {
        $info = self::where('id', $id)->find();
		return $info;
    }

    /**
    * 删除信息
    * @param $id
    * @return array
    */
    public function delFootballSeasonById($id,$type=0)
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
//
//    public function sync(){
//
//        try {
//            $this->autoSync(false);
//        } catch(\Exception $e) {
//            return to_assign(1, '操作失败，原因：'.$e->getMessage());
//        }
//        return to_assign(0,'已成功同步');
//    }
//
//    //自动获取数据同步
//    public function autoSync($isCache=true){
//        $url = "/api/v5/football/season/list";
//        $apiDataInfo = getApiDataInfo($url,$isCache);
//        if(empty($apiDataInfo)){
//            return;
//        }
//        $params = array();
//        if($apiDataInfo["update_field"]=="id"){
//            $params["id"] = $apiDataInfo["max_id"];
//            $params["limit"] = 500;
//        }else{
//            $params["time"] = $apiDataInfo["max_time"];
//            $params["limit"] = 100;
//        }
//        $getApiInfo = getApiInfo($url,$params);
//        if($getApiInfo["code"]==0){
//            $param = [];
//            $ids = [];
//            foreach ($getApiInfo["results"] as $vo){
//
//                $ids[] = $vo["id"];
//
//                $param[] = [
//                    "id"=>$vo["id"],
//                    "competition_id"=>$vo["competition_id"],
//                    "year"=>$vo["year"],
//                    "start_time"=>$vo["start_time"],
//                    "end_time"=>$vo["end_time"],
//                    "is_current"=>$vo["is_current"],
//                    "has_player_stats"=>$vo["has_player_stats"],
//                    "has_team_stats"=>$vo["has_team_stats"],
//                    "has_table"=>$vo["has_table"],
//                    "updated_at"=>$vo["updated_at"],
//                ];
//
//            }
//
//            $voList = self::where("id","in",$ids)->field("id")->select();
//            $ids = [];
//            foreach ($voList as $v){
//                $ids[]=$v->id;
//            }
//
//            foreach ($param as $key=>$vo){
//                if(in_array($vo["id"],$ids)){
//                    self::where('id', $vo['id'])->strict(false)->field(true)->update($vo);
//                    unset($param[$key]);
//                }
//            }
//            //print_r($param);exit;
//            if(!empty($param)){
//                try {
//                    self::strict(false)->field(true)->insertAll($param);
//                } catch(\Exception $e) {
//                    return to_assign(1, '操作失败，原因：'.$e->getMessage());
//                }
//            }
//            setApiCacheData($url,$getApiInfo["query"]);
//        }
//    }
}

