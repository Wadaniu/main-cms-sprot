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
        $compName = $param['compname'] ?? '';
        $matchId = $param['vid'] ?? 0;

//        print_r($compName);
//        print_r($matchId);
//        exit;
        $this->tdk = new Tdk();

        if ($matchId > 0){
            $this->getMatchInfo($matchId);
        }else{
            $this->getMatchList($compName);
        }
        return View::fetch($this->tempPath);

    }


    function getMatchList($compName){
        if($compName){
            $comp = FootballCompetition::where(['short_name_py'=>$compName])->find();//赛事
            if($comp){
                $match = FootballMatch::where(["competition_id"=>$comp->id])->column("id");
                $list = (new MatchVedio())->getList(['type'=>2,'video_type'=>0,'match_id'=>$match],["order"=>'id desc']);
            }else{
                $list = (new MatchVedio())->getList(['type'=>2,'video_type'=>0],["order"=>'id desc']);
            }
        }else{
            $list = (new MatchVedio())->getList(['type'=>2,'video_type'=>0],["order"=>'id desc']);
        }
        $this->getTempPath('luxiang_zuqiu');
        $this->getTdk('luxiang_zuqiu',$this->tdk);

        foreach ($list['data'] as $k=>$v){
            $list['data'][$k]['date']='';
            $list['data'][$k]['team']=[];
            $titleArr = explode(" ",$v['title']);
            if(preg_match("/月/",$titleArr[1])){
                $list['data'][$k]['date'] = str_replace("日","",str_replace("月","-",$titleArr[1]));
            }
            $list['data'][$k]['team'] = explode("vs",$titleArr[3]);
        }


        $shortName = (new FootballCompetition())->where(['status'=>1])->field("short_name_zh,short_name_py")->select()->toArray();
        View::assign("short",$shortName);
        View::assign("list",$list);
        View::assign("index","录像");
        View::assign("href","/luxiang/zuqiu/");
        View::assign("compName",$compName);

    }

    function getMatchInfo($matchId){



        //处理tdk关键字

        $model = new MatchVedio();
        $matchLive = $model->where(['id'=>$matchId])->find()->toArray();
        $this->tdk->title = $matchLive['title'];
        $this->getTempPath("luxiang_zuqiu_detail");
        $this->getTdk('luxiang_zuqiu_detail',$this->tdk);
        View::assign("index","录像介绍");
        View::assign("matchLive",$matchLive);
    }
}