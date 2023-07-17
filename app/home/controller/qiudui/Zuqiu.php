<?php

namespace app\home\controller\qiudui;

use app\commonModel\FootballMatch;
use app\commonModel\FootballTeam;
use app\commonModel\MatchVedio;
use app\home\BaseController;
use app\home\Tdk;
use think\App;
use think\facade\View;

class Zuqiu extends BaseController
{
    const MainLimit = 5;
    public function __construct(App $app)
    {
        parent::__construct($app);
    }
    public function index(){
        $param = $this->parmas;

        $teamid = $param['teamid'] ?? 0;

        $this->tdk = new Tdk();

        if (empty($matchId)){
            $this->getTeamList($param);
        }else{
            $this->getTeamInfo($teamid);
        }
        return View::fetch($this->tempPath);
    }

    protected function getTeamInfo($teamid)
    {
        $this->getTempPath('qiudui_zuqiu_detail');

        //队伍数据
        $team = FootballTeam::where('id',$teamid)->findOrEmpty();

        if ($team->isEmpty()) {
            abort(404, '参数错误');
        }

        //直播数据
        $matchModel = new FootballMatch();
        $matchList = $matchModel->getByTeam($teamid,[['status_id','IN',[1,2,3,4,5,7]],['match_time','>',time()-8000]]);
        if (count($matchList) == 0){
            $matchList = $matchModel->getByTeam($teamid,[['status_id','=',8]],'match_time DESC');
        }

        $videoModel = new MatchVedio();
        $matchId = FootballMatch::whereRAW("home_team_id = :hid OR away_team_id = :aid",['hid'=>$teamid,'aid'=>$teamid])->column("id");

        //录像
        $luxiang = $videoModel->getByMatchId($matchId,0,self::MainLimit,2);
        //集锦
        $jijin = $videoModel->getByMatchId($matchId,0,self::MainLimit);

        $this->tdk->short_name_zh =  empty($team->short_name_zh) ? ($team->name_zh ?? '') : $team->short_name_zh;
        $this->getTdk('qiudui_zuqiu_detail',$this->tdk);
        //$team = $team->toArray();
        $team->competition = (new \app\commonModel\FootballCompetition())->getShortNameZh($team->competition_id);
        $team->coach = (new \app\commonModel\FootballCoach())->getBasketballCoachByTeamid($team->id);
        $team->ball = 'zuqiu';
        $team->venue = (new \app\commonModel\FootballVenue())->getVenueById($team->venue_id);

        $team->intro = $team->name_zh."俱乐部(简称：".$team->short_name_zh.")";
        if(!empty($team->competition)){
            $team->intro.="是由".$team->competition['name_zh']."的足球俱乐部之一，";
        }
        if(!empty($team->venue)){
            $team->intro.=$team->short_name_zh."主场馆是位于".$team->venue['name_zh']."， ";
        }
        if($team->foundation_time){
            $team->intro.=$team->short_name_zh."成立于".$team->foundation_time."，";
        }
        $team->intro.=$team->short_name_zh."球队总评估市值为".$team->market_value."(".$team->market_value_currency.")，". $team->short_name_zh."球员总数为".$team->total_players."人，"
        .$team->short_name_zh."球队队员中，现有国家队球员人数有".$team->national_players."人，
         另外非本土球员为".$team->foreign_players."人，其余都为本土球员，
         ".get_system_config('web', 'title')."提供最新".$team->short_name_zh."的数据和信息，
         ".get_system_config('web', 'title')."同时为您提供最新的".$team->short_name_zh."直播数据。";
        $team->type='team';
        View::assign('data',$matchList);
        View::assign('luxiang',$luxiang);
        View::assign('jijin',$jijin);
//        View::assign('article',$article);
        View::assign('data_info',$team);
        View::assign('article',['data'=>getZiXun(1)]);
    }

    protected function getTeamList($param)
    {
        $this->getTempPath('qiudui_zuqiu');
        //赛程id
        $param['page'] = $param['page'] ?? 1;
        $param['limit'] = 24;
        //篮球数据
        $footballModel = new FootballTeam();
        $data = $footballModel->getList('',$param);
        $data['per_page'] = $param['limit'];
        $data['current_page'] = $param['page'];

        $this->getTdk('qiudui_zuqiu',$this->tdk);
        View::assign('data',$data);
    }
}