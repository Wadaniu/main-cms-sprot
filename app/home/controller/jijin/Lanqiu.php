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
        $param['page'] = (isset($param['page']) && $param['page'])?$param['page']:1;
        $compName = (isset($param['compname']) &&  $param['compname'])?$param['compname']:'';
        $model = new MatchVedio();
        if($compName){
            $comp = BasketballCompetition::where(['short_name_py'=>$compName])->find();//赛事
            if($comp){
                $match = BasketballMatch::where(["competition_id"=>$comp->id])->column("id");
                $list = $model->getList(['type'=>1,'video_type'=>1,'match_id'=>$match],["order"=>'id desc'])->toArray();
            }else{
                $list = $model->getList(['type'=>1,'video_type'=>1],["order"=>'id desc'])->toArray();
            }

        }else{
            $list = $model->getList(['type'=>1,'video_type'=>1],["order"=>'id desc'])->toArray();
        }
        $this->getTempPath('jijin_lanqiu');
        $this->getTdk('jijin_lanqiu',$this->tdk);
        //$list = (new MatchVedio())->getList(['type'=>1,'video_type'=>1],["order"=>'match_id desc']);
        $basketballTeam = new BasketballTeam();
        foreach ($list['data'] as $k=>$v){
            $list['data'][$k]['date']='';
            //$list['data'][$k]['team']=[];
            $titleArr = explode(" ",$v['title']);
            $list['data'][$k]['teamArr'] = [];
            if(isset($titleArr[3])){
                $team = explode("vs",$titleArr[3]);
                if($team){
                    $teamArr = [];
                    foreach ($team  as $t){
                        $teamArr[] = ['name'=>$t,'id'=>$basketballTeam->getTeamInfoByName($t,'name_zh')];
                    }
                    $list['data'][$k]['teamArr'] = $teamArr;
                }
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
        View::assign("index","集锦");
        View::assign("href","/jijin/lanqiu/");
        View::assign("compName",$compName);
        View::assign("param",$param);
    }
    function getMatchInfo($matchId){

        $model = new MatchVedio();
        $matchLive = $model->where(['id'=>$matchId])->find()->toArray();
        $match = (new \app\commonModel\BasketballMatch())->where("id",$matchLive['match_id'])->find();
        $competition_id = 0;
        if($match){
            $competition_id = $match->competition_id;
        }
        $this->tdk->title = $matchLive['title'];
        View::assign("matchLive",$matchLive);
        $this->getTempPath("jijin_lanqiu_detail");
        $this->getTdk('jijin_lanqiu_detail',$this->tdk);
        View::assign("index","集锦介绍");
        View::assign('article',['data'=>getZiXun(2,$competition_id)]);
    }



}