<?php
/**
 * @copyright Copyright (c) 2021 勾股工作室
 * @license https://opensource.org/licenses/Apache-2.0
 * @link https://www.gougucms.com
 */
namespace app\admin\model;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\model;
use think\facade\Cache;

class BasketballMatch extends Model
{
    protected $connection = 'compDataDb';

    public static $CACHE_HOME =  "BasketballMatchHome";
    public static $STATUSID = [
        0=>'比赛异常',
        1=>'未开赛',
        2=>'第一节',
        3=>'第一节完',
        4=>'第二节',
        5=>'第二节完',
        6=>'第三节',
        7=>'第三节完',
        8=>'第四节',
        9=>'加时',
        10=>'已结束',
        11=>'中断',
        12=>'取消',
        13=>'延期',
        14=>'腰斩',
        15=>'待定',
    ];
    public static $KIND = [
        0=>"无",
        1=>"常规赛",
        2=>"季后赛",
        3=>"季前赛",
        4=>"全明星",
        5=>"杯赛",
        6=>"附加赛",
    ];



    /**
    * 获取分页列表
    * @param $where
    * @param $param
    */
    public function getBasketballMatchList($where, $param)
    {
		$rows = empty($param['limit']) ? get_config('app . page_size') : $param['limit'];
		$order = empty($param['order']) ? 'id desc' : $param['order'];
        $list = self::where($where)->order($order)->paginate($rows, false, ['query' => $param])
            ->each(function ($item, $key) {
                if(isset(self::$KIND[$item->kind])){
                    $item->kind = self::$KIND[$item->kind];
                }
                if(isset(self::$STATUSID[$item->status_id])){
                    $item->status_id = self::$STATUSID[$item->status_id];
                }
            $item->match_time = date("Y-m-d H:i",$item->match_time);
            $item->updated_at = date("Y-m-d H:i:s",$item->updated_at);
            if($item->neutral){
                $item->neutral = "是";
            }else{
                $item->neutral = "否";
            }

        });
		return $list;
    }

    /**
    * 添加数据
    * @param $param
    */
    public function addBasketballMatch($param)
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
    public function editBasketballMatch($param)
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
    public function getBasketballMatchById($id)
    {
        $info = self::where('id', $id)->find();
        if(!empty($info))
        $info->status_text = self::$STATUSID[$info->status_id];
		return $info;
    }

    /**
    * 删除信息
    * @param $id
    * @return array
    */
    public function delBasketballMatchById($id,$type=0)
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
     * 获取一周数据
     */
    public function getWeekData($competitionIds = []){

        $startTime =  strtotime(date('Y-m-d', strtotime('-2 days')));
        $endTime = strtotime(date("Y-m-d",strtotime("+5 days")))-1;
        $where[] = ['match_time','between',[$startTime,$endTime]];

        return $this->getMatchInfo($where,$competitionIds);
    }

    /**根据日期比赛id过滤赛程
     * @param array $competitionIds
     * @param string $date
     * @return array|mixed
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getMathchByDate(array $competitionIds = [], string $date = ''){
        $startTime =  strtotime($date.' 00:00:00');
        $endTime = strtotime($date.' 23:59:59');
        $where[] = ['match_time','between',[$startTime,$endTime]];

        return $this->getMatchInfo($where,$competitionIds);
    }

    /**
     * 获取团队信息列表
     * @param $competitionIds
     * @return array|mixed
     */
    public function getTeamListInfo($teamId){
        $where = [];
        $where[] = ["match_time","<=",time()];
        $where[] = ['home_team_id|away_team_id', '=',  $teamId];
        return $this->getMatchInfo($where,[],20,"match_time desc");
    }

    public function getCompetitionListInfo($competitionId){
        $competitionIds[] = $competitionId;
        $where = [];
        $where[] = ["match_time","<=",time()];
        return $this->getMatchInfo($where,$competitionIds,20,"match_time desc");
    }

    /**
     * @param $where
     * @param array $competitionIds
     * @return array|mixed
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getMatchInfo($where,$competitionIds=[],$limit = 0,$order="status_id desc,match_time desc"){
        $key = self::$CACHE_HOME;
        if(!empty($competitionIds)){
            $key .= implode($competitionIds);
        }
        if(!empty($where)){
            $key .= json_encode($where);
        }
        $key .= $limit.$order;
        $data = Cache::get($key);
        if(!empty($data)){
           return $data;
        }

        if(!empty($competitionIds)){
            $where[] = ["competition_id","in",$competitionIds];
        }

        $field="id,competition_id,home_team_id,away_team_id,status_id,match_time";
        $model = self::where($where)
            ->field($field)
            ->order($order);
        if($limit>0){
            $model->limit($limit);
        }
        $data = $model
            ->select()
            ->each(function ($item, $key) {
                if(isset(self::$STATUSID[$item->status_id])){
                    $item->status_text = self::$STATUSID[$item->status_id];
                }
                $basketballCompetition = new  BasketballCompetition();
                $item->competition_text = $basketballCompetition->getShortNameZh($item->competition_id);
                $basketballTeam = new  BasketballTeam();
                $info = $basketballTeam->getShortNameZhLogo($item->home_team_id);
                if(!empty($info)) {
                    $item->home_team_text = $info["short_name_zh"];
                    $item->home_team_logo = $info["logo"];
                }else{
                    $item->home_team_text = "";
                    $item->home_team_logo = "";
                }
                $info = $basketballTeam->getShortNameZhLogo($item->away_team_id);
                if(!empty($info)){
                    $item->away_team_text = $info["short_name_zh"];
                    $item->away_team_logo = $info["logo"];
                }else{
                    $item->away_team_text = "";
                    $item->away_team_logo = "";
                }
                $item->sphere_type="basketball";
            })
            ->toArray();
        Cache::set($key,$data,300);
        return $data;
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
        $url = "/api/v5/basketball/recent/match/list";
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
                $vo["home_scores"] = json_encode($vo["home_scores"]);
                $vo["away_scores"] = json_encode($vo["away_scores"]);
                $vo["coverage"] = json_encode($vo["coverage"]);
                $vo["round"] = json_encode($vo["round"]);
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

    }

    /**
     * 获取直播地址
     */
    public function autoSyncUrlsFree(){
        $url = "/api/v5/basketball/match/stream/urls_free";

        $getApiInfo = getApiInfo($url);
        if($getApiInfo["code"]==0){
            if($getApiInfo["results"]){
                $existLinkIds = [];
                $data = [];
                foreach ($getApiInfo["results"] as $vo){
                    //$data["match_time"] = $vo["match_time"];
                    if(!empty($vo["pc_link"]) || !empty($vo["mobile_link"])){
                        $vo["is_link"] = 1;

                        if (!array_key_exists($vo['match_id'],$existLinkIds)){
                            $existLinkIds[$vo['match_id']] = [
                                'mobile_link'   =>  [$vo['mobile_link']],
                                'pc_link'   =>  [$vo['pc_link']]
                            ];
                        }else{
                            $existLinkIds[$vo['match_id']]['mobile_link'] = array_push($existLinkIds[$vo['match_id']]['mobile_link'],$vo['mobile_link']);
                            $existLinkIds[$vo['match_id']]['pc_link'] = array_push($existLinkIds[$vo['match_id']]['pc_link'],$vo['pc_link']);
                        }
                       // self::where('id', $vo['match_id'])->strict(false)->field(true)->update($data);
                    }
                    $data[] = $vo;
                }
               foreach ($data as $item){
                   if (array_key_exists($item['match_id'],$existLinkIds)){
                        $item['mobile_link'] = json_encode($existLinkIds[$item['match_id']]['mobile_link'],true);
                        $item['pc_link'] = json_encode($existLinkIds[$item['match_id']]['pc_link'],true);
                   }
                   self::where('id', $item['match_id'])->strict(false)->field(true)->update($item);
               }
            }
        }
    }
    /**
     * 视频采集
     */
    public function videoCollection($matchId){
        $info = $this->getBasketballMatchById($matchId);
        if(empty($info)){
            return;
        }
        if($info["replay_type"]>0){
            return;
        }
        $getApiInfo = getApiInfo("/api/v5/basketball/match/stream/video_collection",["id"=>$matchId]);
        if($getApiInfo["code"]==0){
            $results = $getApiInfo["results"];
            if(empty($results)){
                return;
            }

            $data = [
              "type"=>$results["type"],
              "title"=>$results["title"],
              "mobile_link"=>$results["mobile_link"],
              "pc_link"=>$results["pc_link"],
              "cover"=>$results["cover"],
              "duration"=>$results["duration"],
            ];
            self::where('id', $matchId)->strict(false)->field(true)->update($data);
        }
    }



}

