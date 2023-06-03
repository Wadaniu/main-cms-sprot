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
        $this->getTempPath('jijin_zuqiu');
        $this->getTdk('jijin_zuqiu',$this->tdk);
        $param['page'] = (isset($param['page']) && $param['page'])?$param['page']:1;
        $compName = (isset($param['compname']) &&  $param['compname'])?$param['compname']:'';
        $model = new MatchVedio();
        if($compName){
            $comp = FootballCompetition::where(['short_name_py'=>$compName])->find();//赛事
            if($comp){
                $match = FootballMatch::where(["competition_id"=>$comp->id])->column("id");
                $list = $model->getList(['type'=>1,'video_type'=>0,'match_id'=>$match],["order"=>'id desc'])->toArray();
            }else{
                $list = $model->getList(['type'=>1,'video_type'=>0],["order"=>'id desc'])->toArray();
            }
            View::assign('comp',$comp);
        }else{
            $list = $model->getList(['type'=>1,'video_type'=>0],["order"=>'id desc'])->toArray();
        }
        $footballTeam = new FootballTeam();
        foreach ($list['data'] as $k=>$v){
            $list['data'][$k]['date']='';
            $titleArr = explode(" ",$v['title']);
            $list['data'][$k]['teamArr'] = [];
            if(isset($titleArr[3])){
                $team = explode("vs",$titleArr[3]);
                if($team){
                    $teamArr = [];
                    foreach ($team  as $t){
                        $teamArr[] = ['name'=>$t,'id'=>$footballTeam->getTeamInfoByName($t,'name_zh')];
                    }
                    $list['data'][$k]['teamArr'] = $teamArr;
                }
            }
            $list['data'][$k]['short_name_py'] = empty($competition['competition'])?($v['video_type']=='0'?'zuqiu':'lanqiu'):$competition['competition']['short_name_py'];
        }
        $shortName = (new FootballCompetition())->where(['status'=>1])->field("short_name_zh,short_name_py")->select()->toArray();
        View::assign("short",$shortName);
        View::assign("list",$list);
        View::assign("index","集锦");
        View::assign("href","/jijin/zuqiu/");
        View::assign("compName",$compName);
        View::assign("param",$param);
    }

    function getMatchInfo($matchId){
        $model = new MatchVedio();
        $matchLive = $model->where(['id'=>$matchId])->find()->toArray();

        //根据赛程id获取联赛id
        $comp = (new FootballMatch())->where('id',$matchLive['match_id'])->value('competition_id');

        $this->tdk->title = $matchLive['title'];
        View::assign("matchLive",$matchLive);
        $this->getTempPath("jijin_zuqiu_detail");
        $this->getTdk('jijin_zuqiu_detail',$this->tdk);
        View::assign("index","集锦介绍");
        View::assign("comp",['id'=>$comp]);
    }
}