<?php

namespace app\home\controller\luxiang;

use app\home\BaseController;
use think\facade\View;
use think\App;
use app\home\Tdk;
use app\commonModel\MatchVedio;
use app\commonModel\BasketballCompetition;
use app\commonModel\BasketballMatch;
use app\commonModel\FootballMatch;
use app\commonModel\FootballCompetition;
use app\commonModel\FootballMatchInfo;
use app\commonModel\FootballTeam;
use app\commonModel\BasketballTeam;

class Zuqiu extends BaseController
{
    const RouteTag  = 'luxiang_zuqiu';


    public function __construct(App $app)
    {
        parent::__construct($app);
    }
    public function index(){

        $param = get_params();
        //赛程id
        $matchId = $param['vid'] ?? 0;

//        print_r($compName);
//        print_r($matchId);
//        exit;
        $this->tdk = new Tdk();

        if ($matchId > 0){
            $this->getMatchInfo($matchId);
        }else{
            $this->getMatchList($param);
        }
        return View::fetch($this->tempPath);

    }


    function getMatchList($param){
        $this->getTempPath('luxiang_zuqiu');
        $this->getTdk('luxiang_zuqiu',$this->tdk);
        list($list,$competition_id,$param)=getMatchVedio(['type'=>2,'video_type'=>0]);

        View::assign("list",$list);
        View::assign("index","录像");
        View::assign("href","/luxiang/zuqiu/");
        View::assign("param",$param);
        View::assign("jijin",getLuxiangJijin(1,0,$competition_id));

    }

    function getMatchInfo($matchId){



        //处理tdk关键字

        $model = new MatchVedio();
        $matchLive = $model->where(['id'=>$matchId])->find()->toArray();
        $match = (new \app\commonModel\FootballMatch())->where("id",$matchLive['match_id'])->find();
        $competition_id = 0;
        $matchLive['team'] = [];
        $matchLive['match_time'] = '';
        if($match){
            $competition_id = $match->competition_id;
            $matchLive['team'] = $match->getTeamInfo();
            $matchLive['match_time'] = $match->match_time;
        }
        //print_r($competition_id);exit;
        $this->tdk->title = $matchLive['title'];
        $this->getTempPath("luxiang_zuqiu_detail");
        $this->getTdk('luxiang_zuqiu_detail',$this->tdk);

        View::assign("index","录像介绍");
        View::assign("matchLive",$matchLive);
        View::assign('article',['data'=>getZiXun(1,$competition_id)]);
        View::assign('comp',['id'=>$competition_id]);
    }
}