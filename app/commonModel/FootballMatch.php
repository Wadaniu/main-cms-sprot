<?php
/**
 * @copyright Copyright (c) 2021 勾股工作室
 * @license https://opensource.org/licenses/Apache-2.0
 * @link https://www.gougucms.com
 */
namespace app\commonModel;
use think\facade\Cache;
use think\model;

class FootballMatch extends Model
{
    protected $connection = 'compDataDb';

    public static $CACHE_HOME =  "FootballMatchHome";
    public static $STATUSID = [
        0=>'比赛异常',
        1=>'未开赛',
        2=>'上半场',
        3=>'中场',
        4=>'下半场',
        5=>'加时赛',
        7=>'点球决战',
        8=>'完场',
        9=>'推迟',
        10=>'中断',
        11=>'腰斩',
        12=>'取消',
        13=>'待定',
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
    public function getFootballMatchList($where, $param)
    {
		$rows = empty($param['limit']) ? get_config('app . page_size') : $param['limit'];
		$order = empty($param['order']) ? 'id desc' : $param['order'];
        $list = self::where($where)
            ->field('id,season_id,home_team_id,away_team_id,status_id,match_time,neutral,note,home_scores,away_scores,home_position,away_position,coverage,venue_id,referee_id,related_id,agg_score,round,environment,updated_at,comp,home,away,mobile_link,pc_link,type,title,cover,duration')
            ->order($order)
            ->paginate($rows, false, ['query' => $param])
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
    public function addFootballMatch($param)
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
    public function editFootballMatch($param)
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
    public function getFootballMatchById($id)
    {
        $info = self::where('id', $id)->find();
		return $info;
    }

    /**
    * 删除信息
    * @param $id
    * @return array
    */
    public function delFootballMatchById($id,$type=0)
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
    public function getWeekData($competitionIds = []): array
    {
        $startTime =  strtotime(date('Y-m-d', strtotime('-1 days')));
        $endTime = strtotime(date("Y-m-d",strtotime("+4 days")))-1;
        $where[] = ['match_time','between',[$startTime,$endTime]];
        $where[] = ['status_id','IN',[1,2,3,4,5,7,8]];

        return $this->getMatchInfo($where,$competitionIds);
    }

    /**
     * 获取过去一周数据
     */
    public function getBeWeekData($competitionIds = []): array
    {
        $startTime =  strtotime(date('Y-m-d', time()));
        $endTime = strtotime(date("Y-m-d",strtotime("+7 days")))-1;
        $where[] = ['match_time','between',[$startTime,$endTime]];

        return $this->getMatchInfo($where,$competitionIds);
    }

    /**根据日期比赛id过滤赛程
     * @param array $competitionIds
     * @param string $date
     * @return array|mixed
     */
    public function getMatchByDate(array $competitionIds = [], string $startDate = '',$endDate = ''){
        $startTime =  strtotime($startDate.' 00:00:00');
        $endTime = strtotime($endDate.' 23:59:59');
        $where[] = ['match_time','between',[$startTime,$endTime]];
        $where[] = ['status_id','=',8];
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

    public function getCompetitionListInfo($competitionId,$limit = 20): array
    {
        $competitionIds = [];
        if ($competitionId > 0){
            $competitionIds[] = $competitionId;
        }
        $where = [];
        //比赛时间大于当前时间-5400s
        $where[] = ["match_time",">=",time() - 5400];
        return $this->getMatchInfo($where,$competitionIds,$limit,"match_time desc");
    }

    /**
     * 获取一周数据
     */
    public function getMatchInfo($where,$competitionIds=[],$limit = 50,$order="status_id desc,match_time asc"): array
    {
        $key = self::$CACHE_HOME;
        if(!empty($competitionIds)){
            $key .= implode($competitionIds);
        }
        if(!empty($where)){
            $key .= json_encode($where);
        }
        $key .= $limit.$order;
        $data = Cache::store('common_redis')->get($key);
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

                $footballCompetition = new  FootballCompetition();
                $comp = $footballCompetition->getShortNameZh($item->competition_id);
                $item->competition_text = $comp['short_name_zh']??'';
                $item->comp_py = $comp['short_name_py']??'';

                $footballTeam = new  FootballTeam();
                $info = $footballTeam->getShortNameZhLogo($item->home_team_id);
                $item->home_team_text = $info["short_name_zh"]??"";
                $item->home_team_logo = $info["logo"]??"";

                $info = $footballTeam->getShortNameZhLogo($item->away_team_id);
                $item->away_team_text = $info["short_name_zh"]??"";
                $item->away_team_logo = $info["logo"]??"";
                $item->sphere_type="zuqiu";
            })->toArray();

        Cache::store('common_redis')->set($key,$data,120);
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
    public function autoSync($is_increment = true){
        if ($is_increment){
            $url = "/api/v5/football/recent/match/list";
        }else{
            $url = '/api/v5/football/match/list';
        }

        $apiDataInfo = getApiDataInfo($url);
        if(empty($apiDataInfo)){
            return;
        }
        $params = array();
        if($apiDataInfo["update_field"]=="id"){
            $params["id"] = $apiDataInfo["max_id"];
            $params["limit"] = 1000;
        }else{
            $params["time"] = $apiDataInfo["max_time"];
            $params["limit"] = 500;
        }

        $getApiInfo = getApiInfo($url,$params);

        if($getApiInfo["code"]==0){
            $param = [];
            $ids = [];
            foreach ($getApiInfo["results"] as $vo){

                $ids[] = $vo["id"];

                if(!isset($vo["home_scores"])){
                    $vo["home_scores"] = array();
                }
                if(!isset($vo["away_scores"])){
                    $vo["away_scores"] = array();
                }
                if(!isset($vo["coverage"])){
                    $vo["coverage"] = array();
                }
                if(!isset($vo["agg_score"])){
                    $vo["agg_score"] = array();
                }
                if(!isset($vo["round"])){
                    $vo["round"] = array();
                }
                if(!isset($vo["environment"])){
                    $vo["environment"] = array();
                }
                $vo["home_scores"] = json_encode($vo["home_scores"]);
                $vo["away_scores"] = json_encode($vo["away_scores"]);
                $vo["coverage"] = json_encode($vo["coverage"]);
                $vo["agg_score"] = json_encode($vo["agg_score"]);
                $vo["round"] = json_encode($vo["round"]);
                $vo["environment"] = json_encode($vo["environment"]);

                $param[] = [
                    "id"=>$vo["id"],
                    "season_id"=>$vo["season_id"],
                    "competition_id"=>$vo["competition_id"],
                    "home_team_id"=>$vo["home_team_id"],
                    "away_team_id"=>$vo["away_team_id"],
                    "status_id"=>$vo["status_id"],
                    "match_time"=>$vo["match_time"],
                    "neutral"=>isset($vo["neutral"])?$vo["neutral"]:0,
                    "note"=>$vo["note"],
                    "home_scores"=>$vo["home_scores"],
                    "away_scores"=>$vo["away_scores"],
                    "home_position"=>$vo["home_position"],
                    "away_position"=>$vo["away_position"],
                    "coverage"=>$vo["coverage"],
                    "venue_id"=>$vo["venue_id"],
                    "referee_id"=>$vo["referee_id"],
                    "related_id"=>$vo["related_id"],
                    "agg_score"=>$vo["agg_score"],
                    "round"=>$vo["round"],
                    "environment"=>$vo["environment"],
                    "updated_at"=>$vo["updated_at"],
                ];

            }

            $model = \think\facade\Db::connect('compDataDb')->name("sphere_query_update");
            $syncInfo = $model->where('id','syncFootballMatchInfoList')->findOrEmpty();

            if (empty($syncInfo)){
                $idList = $ids;
                $insert = [
                    'parmars' => json_encode($idList,true),
                    'id'    =>  'syncFootballMatchInfoList'
                ];
                $model->insert($insert);
            }else{
                $idList = array_unique(array_merge($ids,json_decode($syncInfo['parmars'],true)));
                $syncInfo['parmars'] = json_encode($idList,true);
                $model->where('id','syncFootballMatchInfoList')->update($syncInfo);
            }
            Cache::set('syncFootballMatchInfoList',$idList);

            $voList = self::where("id","in",$ids)->field("id")->select()->toArray();
            $ids = array_column($voList,'id');

            $updateArr = [];
            foreach ($param as $key=>$vo){
                if(in_array($vo["id"],$ids)){
                    $updateArr[] = $vo;
                    unset($param[$key]);
                }
            }

            try {
                if (!empty($updateArr)){
                    self::saveAll($updateArr);
                }
                if(!empty($param)){
                    self::strict(false)->field(true)->insertAll($param);
                }
            } catch (\Exception $e) {
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

    /**
     * 获取直播地址
     */
    public function autoSyncUrlsFree(){
        $url = "/api/v5/football/match/stream/urls_free";

        $getApiInfo = getApiInfo($url);
        if(!empty($getApiInfo)){
            if($getApiInfo["code"]==0){
                if($getApiInfo["results"]){
                    $existLinkIds = [];
                    $data = [];
                    foreach ($getApiInfo["results"] as $vo){
                        //$data["match_time"] = $vo["match_time"];
                        if(!empty($vo["pc_link"]) || !empty($vo["mobile_link"])){
                            $vo["is_link"] = 1;

                            //处理多个地址
                            if (array_key_exists($vo['match_id'],$existLinkIds)){
                                $existLinkIds[$vo['match_id']]['mobile_link'] = array_push($existLinkIds[$vo['match_id']]['mobile_link'],$vo['mobile_link']);
                                $existLinkIds[$vo['match_id']]['pc_link'] = array_push($existLinkIds[$vo['match_id']]['pc_link'],$vo['pc_link']);

                            }else{
                                $existLinkIds[$vo['match_id']] = [
                                    'mobile_link'   =>  [$vo['mobile_link']],
                                    'pc_link'   =>  [$vo['pc_link']]
                                ];
                            }
                            // self::where('id', $vo['match_id'])->strict(false)->field(true)->update($data);
                        }
                        $data[] = $vo;
                    }
                    foreach ($data as $item){
                        if (array_key_exists($item['match_id'],$existLinkIds)){
                            //将多条地址写入
                            $item['mobile_link'] = json_encode($existLinkIds[$item['match_id']]['mobile_link'],true);
                            $item['pc_link'] = json_encode($existLinkIds[$item['match_id']]['pc_link'],true);
                        }
                        self::where('id', $item['match_id'])->strict(false)->field(true)->update($item);
                    }
                }
            }
        }
    }

    public function getMatchLive($id)
    {
        $info = self::field('id,is_link,mobile_link,pc_link')->where('id', $id)->where('status_id','IN',[1,2,3,4,5,7])->findOrEmpty();
        return $info;
    }

    public function getFootballMatchIndex($ids = [])
    {
        //获取小于等于当前时间的数据
        $time = strtotime('-1 hours');
        $field = 'id,status_id,competition_id,home_team_id,away_team_id,match_time';
        //获取大于当前时间
        $data = self::field($field)
            ->where('competition_id','IN',$ids)
            ->where('status_id','IN',[1,2,3,4,5,7])
            ->where('match_time','>=',$time)
            ->order('match_time','ASC')
            ->limit(20)->select()->each(function ($item, $key) {
                if(isset(self::$STATUSID[$item->status_id])){
                    $item->status_text = self::$STATUSID[$item->status_id];
                }

                $footballCompetition = new  FootballCompetition();
                $comp = $footballCompetition->getShortNameZh($item->competition_id);
                $item->competition_text = $comp['short_name_zh']??'';
                $item->comp_py = $comp['short_name_py']??'';

                $footballTeam = new  FootballTeam();
                $info = $footballTeam->getShortNameZhLogo($item->home_team_id);
                $item->home_team_text = $info["short_name_zh"]??"";
                $item->home_team_logo = $info["logo"]??"";

                $info = $footballTeam->getShortNameZhLogo($item->away_team_id);
                $item->away_team_text = $info["short_name_zh"]??"";
                $item->away_team_logo = $info["logo"]??"";
                $item->sphere_type="zuqiu";
            })
            ->toArray();
        return $data;
    }

    public function getByTeam($id)
    {
        $key = 'footballTeamMatch'.$id;
        $data = Cache::store('common_redis')->get($key);
        if(!empty($data)){
            return $data;
        }
        //获取三十天内时间
        $startTime = \time() - 86400 * 30;

        $data = self::field('id,status_id,competition_id,home_team_id,away_team_id,match_time')
            ->where('match_time','>',$startTime)
            ->whereRAW("home_team_id = :id OR away_team_id = :id",$id)
            ->order('match_time','ASC')->select()
            ->each(function ($item, $key) {
            if(isset(self::$STATUSID[$item->status_id])){
                $item->status_text = self::$STATUSID[$item->status_id];
            }

            $footballCompetition = new  FootballCompetition();
            $comp = $footballCompetition->getShortNameZh($item->competition_id);
                $item->competition_text = $comp['short_name_zh']??'';
                $item->comp_py = $comp['short_name_py']??'';

            $footballTeam = new  FootballTeam();
            $info = $footballTeam->getShortNameZhLogo($item->home_team_id);
            $item->home_team_text = $info["short_name_zh"]??"";
            $item->home_team_logo = $info["logo"]??"";

            $info = $footballTeam->getShortNameZhLogo($item->away_team_id);
            $item->away_team_text = $info["short_name_zh"]??"";
            $item->away_team_logo = $info["logo"]??"";
            $item->sphere_type="zuqiu";
        })->toArray();

        Cache::store('common_redis')->set($key,$data,120);
        return $data;
    }

    /**
     * 获取历史一周数据
     */
    public function getWeekHistoryData($competitionIds = [],$limit = 0,$order = "status_id desc,match_time asc"): array
    {

        $startTime =  strtotime(date('Y-m-d', strtotime('-6 days')));
        $endTime = strtotime(date("Y-m-d",time()));
        $where[] = ['match_time','between',[$startTime,$endTime]];

        return $this->getMatchInfo($where,$competitionIds,$limit,$order);
    }
}

