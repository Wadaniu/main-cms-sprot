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

        if ($matchId > 0){
            $this->getMatchInfo($matchId);
        }else{
            $this->getMatchList($param);
        }
        return View::fetch($this->tempPath);

    }


    function getMatchList($param){
        $this->getTempPath('luxiang_lanqiu');
        $this->getTdk('luxiang_lanqiu',$this->tdk);
        list($list,$competition_id,$param)=getMatchVedio(['type'=>2,'video_type'=>1]);
        View::assign("list",$list);
        View::assign("index","录像");
        View::assign("href","/luxiang/lanqiu/");
        View::assign("param",$param);
        View::assign("comp",['id'=>$competition_id]);
        View::assign("jijin",getLuxiangJijin(1,1,$competition_id));
    }

    function getMatchInfo($matchId){
        list($matchLive,$competition_id) = getMatchVedioById($matchId);
        //处理tdk关键字
        $this->tdk->title = $matchLive['title'];
        View::assign("matchLive",$matchLive);
        $this->getTempPath("luxiang_lanqiu_detail");
        $this->getTdk('luxiang_lanqiu_detail',$this->tdk);
        View::assign("index","录像介绍");
        View::assign('article',['data'=>getZiXun(2,$competition_id)]);
        View::assign('comp',['id'=>$competition_id]);
    }
}