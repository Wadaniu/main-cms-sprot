<?php

namespace app\home\controller\qiudui;

use app\commonModel\BasketballMatch;
use app\commonModel\BasketballTeam;
use app\commonModel\MatchVedio;
use app\home\BaseController;
use app\home\Tdk;
use think\App;
use think\facade\View;

class Lanqiu extends BaseController
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

        if ($teamid > 0){
            $this->getTeamInfo($teamid);
        }else{
            $this->getTeamList($param);
        }
        return View::fetch($this->tempPath);
    }

    protected function getTeamInfo($teamid)
    {
        $this->getTempPath('qiudui_lanqiu_detail');

        //队伍数据
        $team = BasketballTeam::where('id',$teamid)->findOrEmpty();

        if ($team->isEmpty()) {
            abort(404, '参数错误');
        }

        //直播数据
        $matchModel = new BasketballMatch();
        $matchList = $matchModel->getByTeam($teamid,[['status_id','IN',[1,2,3,4,5,7,8,9]],['match_time','>',time()-8000]]);
        if (count($matchList) == 0){
            $matchList = $matchModel->getByTeam($teamid,[['status_id','=',10]],'match_time DESC');
        }

        $videoModel = new MatchVedio();
        $matchId = BasketballMatch::whereRAW("home_team_id = :hid OR away_team_id = :aid",['hid'=>$teamid,'aid'=>$teamid])->column("id");
        //录像
        $luxiang = $videoModel->getByMatchId($matchId,1,self::MainLimit,2);
        //集锦
        $jijin = $videoModel->getByMatchId($matchId,1,self::MainLimit);

        $this->tdk->short_name_zh =  empty($team->short_name_zh) ? ($team->name_zh ?? '') : $team->short_name_zh;
        $this->getTdk('qiudui_lanqiu_detail',$this->tdk);

        $team->competition = (new \app\commonModel\BasketballCompetition())->getShortNameZh($team->competition_id);
        $team->coach = (new \app\commonModel\BasketballCoach())->getBasketballCoachByTeamid($team->id);
        $team->ball = 'lanqiu';
        $team->venue = (new \app\commonModel\BasketballVenue())->getVenueById($team->venue_id);


        $team->intro = $team->name_zh."队(简称： ".$team->short_name_zh."队)，";
        if(!empty($team->competition)){
            $team->intro.=$team->name_zh."队所在联赛是".$team->competition['name_zh']."联赛，";
        }
        if(!empty($team->coach)){
            $team->intro.="现".$team->name_zh."队主教练是由".$team->coach['name_zh']."带领，";
        }
        if(!empty($team->venue)){
            $team->intro.=$team->name_zh."队是".$team->venue['city']."的职业篮球队，";
        }
        $team->intro.=get_system_config('web', 'title')."为您提供最新".$team->name_zh."队的数据和信息，".get_system_config('web', 'title')."同时为您提供最新的".$team->name_zh."队直播数据。";
        $team->type='team';


        View::assign('data',$matchList);
        View::assign('luxiang',$luxiang);
        View::assign('jijin',$jijin);
//        View::assign('article',$article);
        View::assign('data_info',$team);
        View::assign('article',['data'=>getZiXun(2)]);
    }

    protected function getTeamList($param)
    {
        $this->getTempPath('qiudui_lanqiu');
        //赛程id
        $param['page'] = $param['page'] ?? 1;
        $param['limit'] = 24;
        //篮球数据
        $basketballModel = new BasketballTeam();
        $data = $basketballModel->getList('',$param);
        $data['per_page'] = $param['limit'];
        $data['current_page'] = $param['page'];

        $this->getTdk('qiudui_lanqiu',$this->tdk);
        View::assign('data',$data);
    }
}