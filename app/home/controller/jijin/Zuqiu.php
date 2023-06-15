<?php

namespace app\home\controller\jijin;

use app\home\BaseController;
use think\facade\View;
use think\App;
use app\home\Tdk;
use app\commonModel\MatchVedio;
use app\commonModel\FootballCompetition;
use app\commonModel\FootballMatch;
use app\commonModel\FootballMatchInfo;
use app\commonModel\FootballTeam;
use app\commonModel\BasketballTeam;

class Zuqiu extends BaseController
{
    const RouteTag  = 'jijin_zuqiu';


    public function __construct(App $app)
    {
        parent::__construct($app);
    }
    public function index(){
        $param = $this->parmas;
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
        $this->getTempPath('jijin_zuqiu');
        list($list,$competition_id,$param,$short_name_zh)=getMatchVedio(['type'=>1,'video_type'=>0]);
        $this->tdk->short_name_zh = $short_name_zh==''?'足球':$short_name_zh;
        $this->getTdk('jijin_zuqiu',$this->tdk);
        View::assign("list",$list);
        View::assign("index","集锦");
        View::assign("href","/jijin/zuqiu/");
        //View::assign("compName",$compName);
        View::assign("param",$param);
        View::assign("comp",['id'=>$competition_id]);
        View::assign("luxiang",getLuxiangJijin(2,0,$competition_id,4));
    }

    function getMatchInfo($matchId){
        list($matchLive,$competition_id) = getMatchVedioById($matchId);
        $this->tdk->title = $matchLive['title'];
        View::assign("matchLive",$matchLive);
        $this->getTempPath("jijin_zuqiu_detail");
        $this->getTdk('jijin_zuqiu_detail',$this->tdk);
        View::assign("index","集锦介绍");
        View::assign('article',['data'=>getZiXun(1,$competition_id)]);
        View::assign("comp",['id'=>$competition_id]);
    }
}