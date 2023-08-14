<?php

namespace app\home\controller\yuce;

use app\home\BaseController;
use app\home\Tdk;
use think\App;
use think\facade\View;
use app\commonModel\Article;
use app\commonModel\FootballCompetition;
use app\commonModel\Admin;
use app\commonModel\ArticleKeywords;
use app\commonModel\FootballMatch;
use app\commonModel\FootballMatchInfo;

class Zuqiu extends BaseController
{
    public function __construct(App $app)
    {
        parent::__construct($app);
    }
    public function index(){
        $param = $this->parmas;

        $teamid = $param['aid'] ?? 0;

        $this->tdk = new Tdk();

        if (!empty($teamid)){
            $this->getCompInfo($teamid);
        }else{
            $this->getMatchList();
        }
        return View::fetch($this->tempPath);
    }

    protected function getCompInfo($matchId)
    {
        if(!is_numeric($matchId)){
            throw new \think\exception\HttpException(404, '找不到页面');
        }
        $comp = FootballMatch::where('id',$matchId)->findOrEmpty();
        if ($comp->isEmpty()) {
            throw new \think\exception\HttpException(404, '找不到页面');
        }
        $FootballMatchInfoModel = new FootballMatchInfo();
        $matchInfo = $FootballMatchInfoModel->getByMatchId($matchId);

        $model = new FootballMatch();
        $matchLive = $model->getMatchLive($matchId);

        if ($matchLive->isEmpty()){
            $matchLive['mobile_link'] = [];
            $matchLive['pc_link'] = [];
        }else{
            $matchLive['mobile_link'] = json_decode($matchLive['mobile_link'],true);
            $matchLive['pc_link'] = json_decode($matchLive['pc_link'],true);
        }

        //历史交锋
        $analysis = [
            'info'      =>  is_null($matchInfo['info']) ? [] : json_decode($matchInfo['info'],true),
            'future'    =>  is_null($matchInfo['future']) ? [] : json_decode($matchInfo['future'],true),
            'history'   =>  is_null($matchInfo['history']) ? [] : json_decode($matchInfo['history'],true),
        ];
        //队伍统计
        $teamStats = is_null($matchInfo['team_stats']) ? [] : json_decode($matchInfo['team_stats'],true);
        $this->getTempPath('yuce_zuqiu_detail');
        $info = $comp->toArray();
        $comp = FootballCompetition::where("id",$info["competition_id"])->find()->toArray();
        $footballTeam = new \app\commonModel\FootballTeam();
        $home = $footballTeam->getShortNameZhLogo($info["home_team_id"]);
        $away = $footballTeam->getShortNameZhLogo($info["away_team_id"]);
        $this->tdk->home_team_name = $home['name_zh']??'';
        $this->tdk->away_team_name = $away['name_zh']??'';
        $this->tdk->match_time = date("Y-m-d H:i");
        $info['home'] = $home;
        $info['away'] = $away;
        $this->getTdk('yuce_zuqiu_detail',$this->tdk);
        View::assign("info",$info);
        View::assign("comp",$comp);
        View::assign("ball",'zuqiu');
        View::assign("analysis",$analysis);
        View::assign("teamStats",$teamStats);
        View::assign("matchLive",$matchLive);

    }

    protected function getMatchList()
    {
        $param = $this->parmas;

        $param['page'] = isset($param['page'])?$param['page']:1;
        $param['limit'] = 10;
        $model = (new FootballMatch());
        $where = [
            ["match_time",">=",time()],
            ["forecast","NOT NULL","NOT NULL"]
        ];
        if(isset($param['compname']) && $param['compname']){
            $competition = FootballCompetition::where("short_name_py",$param['compname'])->find();
            if($competition){
                $where[] = ["competition_id","in",[$competition->id]];
            }
            View::assign('comp',$competition);
        }
        $list = $model->getFootballMatchList($where,["order"=>"match_time asc"])->toArray();
        $footballTeam = new \app\commonModel\FootballTeam();
        $comp = new FootballCompetition();
        foreach ($list['data'] as $k=>$v){
            $list["data"][$k]['home'] = [];
            $list["data"][$k]['away'] = [];
            $str = "主队vs客队:预测分析,比分预测,在线直播,比赛结果";
            $home = $footballTeam->getShortNameZhLogo($v["home_team_id"]);
            $list["data"][$k]['home'] = $home;
            $away = $footballTeam->getShortNameZhLogo($v["away_team_id"]);
            $list["data"][$k]['away'] = $away;
            $list['data'][$k]['comp'] = $comp->getShortNameZh($v['competition_id']);
        }
        $list['current_page'] = $param['page'];
        $this->getTdk('yuce_zuqiu',$this->tdk);
        View::assign("list",$list);
        View::assign('param',$param);
        View::assign("ball",'zuqiu');
        $this->getTempPath('yuce_zuqiu');

    }
}