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
            $this->getMatchInfo($matchId);
        }else{
            $this->getMatchList($param);
        }
        return View::fetch($this->tempPath);
    }


    function getMatchList($param){
        $this->getTempPath('jijin_lanqiu');
        $this->getTdk('jijin_lanqiu',$this->tdk);
        list($list,$competition_id,$param)=getMatchVedio(['type'=>1,'video_type'=>1]);
        View::assign("list",$list);
        View::assign("index","集锦");
        View::assign("href","/jijin/lanqiu/");
        //View::assign("compName",$compName);
        View::assign("param",$param);
        View::assign("luxiang",getLuxiangJijin(2,1,$competition_id,4));
    }
    function getMatchInfo($matchId){

        $model = new MatchVedio();
        $matchLive = $model->where(['id'=>$matchId])->find()->toArray();
        $match = (new \app\commonModel\BasketballMatch())->where("id",$matchLive['match_id'])->find();
        $competition_id = 0;
        $matchLive['team'] = [];
        $matchLive['match_time'] = '';
        if($match){
            $competition_id = $match->competition_id;
            $matchLive['team'] = $match->getTeamInfo();
            $matchLive['match_time'] = $match->match_time;
        }
        $this->tdk->title = $matchLive['title'];
        View::assign("matchLive",$matchLive);
        $this->getTempPath("jijin_lanqiu_detail");
        $this->getTdk('jijin_lanqiu_detail',$this->tdk);
        View::assign("index","集锦介绍");
        View::assign("comp",['id'=>$competition_id]);
        View::assign('article',['data'=>getZiXun(2,$competition_id)]);
    }



}