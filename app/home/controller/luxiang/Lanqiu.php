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


        $param = get_params();
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
        $competition_id=0;
        $param['page'] = (isset($param['page']) && $param['page'])?$param['page']:1;
        $compName = (isset($param['compname']) &&  $param['compname'])?$param['compname']:'';
        $model = new MatchVedio();

        if($compName){
            $comp = BasketballCompetition::where(['short_name_py'=>$compName])->find();//赛事
            if($comp){
                $match = BasketballMatch::where(["competition_id"=>$comp->id])->column("id");
                $list = $model->getList(['type'=>2,'video_type'=>1,'match_id'=>$match],["order"=>'id desc'])->toArray();
                $competition_id = $comp->id;
            }else{
                $list = $model->getList(['type'=>2,'video_type'=>1],["order"=>'id desc'])->toArray();
            }
            View::assign('comp',$comp);
        }else{
            $list = $model->getList(['type'=>2,'video_type'=>1],["order"=>'id desc'])->toArray();
        }

        $this->getTempPath('luxiang_lanqiu');
        $this->getTdk('luxiang_lanqiu',$this->tdk);
        $basketballTeam = new BasketballTeam();
        foreach ($list['data'] as $k=>$v){
            $list['data'][$k]['date']='';
            $titleArr = explode(" ",$v['title']);
            $team = explode("vs",$titleArr[3]);
            if($team){
                $teamArr = [];
                foreach ($team  as $t){
                    $teamArr[] = ['name'=>$t,'id'=>$basketballTeam->getTeamInfoByName($t,'name_zh')];
                }
                $list['data'][$k]['teamArr'] = $teamArr;
            }
            $competition = $model->getCompetitionInfo($v['id']);
            if(isset($competition['match']['match_time'])){
                $list['data'][$k]['date'] = date('m-d',$competition['match']['match_time']);
            }
            $list['data'][$k]['short_name_py'] = empty($competition['competition'])?($v['video_type']=='0'?'zuqiu':'lanqiu'):$competition['competition']['short_name_py'];
        }
        $shortName = (new BasketballCompetition())->where(['status'=>1])->field("short_name_zh,short_name_py")->select()->toArray();
        View::assign("short",$shortName);
        View::assign("list",$list);
        View::assign("index","录像");
        View::assign("href","/luxiang/lanqiu/");
        View::assign("compName",$compName);
        View::assign("param",$param);
        View::assign("jijin",getLuxiangJijin(1,1,$competition_id));
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