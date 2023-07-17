<?php

namespace app\home\controller\luxiang;

use app\home\BaseController;
use think\facade\View;
use think\App;
use app\home\Tdk;
use app\commonModel\MatchVedio;
use app\commonModel\BasketballCompetition;
use app\commonModel\BasketballMatch;
use app\commonModel\BasketballMatchInfo;
use app\commonModel\FootballTeam;
use app\commonModel\BasketballTeam;

class Lanqiu extends BaseController
{

    const RouteTag  = 'luxiang_lanqiu';


    public function __construct(App $app)
    {
        parent::__construct($app);
    }
    public function index(){
        $param = $this->parmas;
        //赛程id
        $matchId = $param['vid'] ?? 0;

        $this->tdk = new Tdk();

        if (!empty($matchId)){
            $this->getMatchInfo($matchId,$param['compname']);
        }else{
            $this->getMatchList($param);
        }
        return View::fetch($this->tempPath);

    }


    function getMatchList($param){
        $this->getTempPath('luxiang_lanqiu');
        list($list,$competition_id,$param,$short_name_zh)=getMatchVedio(['type'=>2,'video_type'=>1]);
        $this->tdk->short_name_zh =  $short_name_zh==''?'蓝球':$short_name_zh;
        $this->getTdk('luxiang_lanqiu',$this->tdk);
        View::assign("list",$list);
        View::assign("index","录像");
        View::assign("href","/luxiang/lanqiu/");
        View::assign("param",$param);
        View::assign("comp",['id'=>$competition_id]);
        View::assign("jijin",getLuxiangJijin(1,1,$competition_id));
    }

    function getMatchInfo($matchId,$comp){
        list($matchLive,$competition_id) = getMatchVedioById($matchId,$comp);
        //处理tdk关键字
        $this->tdk->title = $matchLive['title'];
        $this->tdk->short_name_zh = $matchLive['short_name_zh'];
        $this->tdk->home_team_name = $matchLive['team']['home_team']['name_zh']??'';
        $this->tdk->away_team_name = $matchLive['team']['away_team']['name_zh']??'';
        View::assign("matchLive",$matchLive);
        $this->getTempPath("luxiang_lanqiu_detail");
        $this->getTdk('luxiang_lanqiu_detail',$this->tdk);
        View::assign("index","录像介绍");
        View::assign('article',['data'=>getZiXun(2,$competition_id)]);
        View::assign('comp',['id'=>$competition_id]);
    }
}