<?php

namespace app\home\controller\jijin;

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
    const RouteTag  = 'jijin_lanqiu';
    /**
     * @var mixed
     */

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
            $this->getMatchInfo($matchId,$param['compname']);
        }else{
            $this->getMatchList($param);
        }
        return View::fetch($this->tempPath);
    }


    function getMatchList($param){
        list($list,$competition_id,$param,$short_name_zh)=getMatchVedio(['type'=>1,'video_type'=>1]);
        $this->getTempPath('jijin_lanqiu');
        $this->tdk->short_name_zh = $short_name_zh==''?'蓝球':$short_name_zh;
        $this->getTdk('jijin_lanqiu',$this->tdk);
        View::assign("list",$list);
        View::assign("index","集锦");
        View::assign("href","/jijin/lanqiu/");
        //View::assign("compName",$compName);
        View::assign("param",$param);
        View::assign("comp",['id'=>$competition_id]);
        View::assign("luxiang",getLuxiangJijin(2,1,$competition_id,4));
    }
    function getMatchInfo($matchId,$comp){

        list($matchLive,$competition_id) = getMatchVedioById($matchId,$comp);
        $this->tdk->title = $matchLive['title'];
        $this->tdk->short_name_zh = $matchLive['short_name_zh'];
        $this->tdk->home_team_name = $matchLive['team']['home_team']['name_zh']??'';
        $this->tdk->away_team_name = $matchLive['team']['away_team']['name_zh']??'';
        View::assign("matchLive",$matchLive);
        $this->getTempPath("jijin_lanqiu_detail");
        $this->getTdk('jijin_lanqiu_detail',$this->tdk);
        View::assign("index","集锦介绍");
        View::assign("comp",['id'=>$competition_id]);
        View::assign('article',['data'=>getZiXun(2,$competition_id)]);
    }



}