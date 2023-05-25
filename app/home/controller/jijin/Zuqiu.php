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
        $compName = $param['compname'] ?? '';
        $matchId = $param['v_id'] ?? 0;

        $this->tdk = new Tdk();

        if ($matchId > 0){
            $this->getMatchInfo($matchId);
        }else{
            $this->getMatchList($compName);
        }



        return View::fetch($this->tempPath);

    }


    function getMatchList($compName){
        $this->getTempPath('jijin_zuqiu');
        $this->getTdk('jijin_zuqiu',$this->tdk);
        if($compName){
            $comp = FootballCompetition::where(['short_name_py'=>$compName])->find();//赛事
            $match = FootballMatch::where(["competition_id"=>$comp->id])->column("id");
            $list = (new MatchVedio())->getList(['type'=>1,'video_type'=>0,'match_id'=>$match],["order"=>'match_id desc']);
        }else{
            $list = (new MatchVedio())->getList(['type'=>1,'video_type'=>0],["order"=>'match_id desc']);
        }
        //$list = (new MatchVedio())->getList(['type'=>1,'video_type'=>0],["order"=>'match_id desc']);
        foreach ($list['data'] as $k=>$v){
            $list['data'][$k]['date']='';
            $list['data'][$k]['team']=[];
            $titleArr = explode(" ",$v['title']);
            //print_r($titleArr);
            if(preg_match("/月/",$titleArr[1])){
                $list['data'][$k]['date'] = str_replace("日","",str_replace("月","-",$titleArr[1]));
            }
            if(isset($titleArr[2]) && preg_match('/-/',$titleArr[2])){
                $team = explode("-",$titleArr[2]);
                foreach ($team as $t){
                    $list['data'][$k]['team'][] = preg_replace("/[0-9]/","",$t);
                }
            }

        }
        $shortName = (new FootballCompetition())->where(['status'=>1])->field("short_name_zh,short_name_py")->select()->toArray();
        View::assign("short",$shortName);
        View::assign("list",$list);
        View::assign("index","录像");
        View::assign("href","/jijin/zuqiu/");
        View::assign("compName",$compName);
    }

    function getMatchInfo($matchId){
        $FootballMatchInfoModel = new FootballMatchInfo();
        $matchInfo = $FootballMatchInfoModel->getByMatchId($matchId);
        //历史交锋
        $analysis = [
            'info'      =>  is_null($matchInfo['info']) ? [] : json_decode($matchInfo['info'],true),
            'future'    =>  is_null($matchInfo['future']) ? [] : json_decode($matchInfo['future'],true),
            'history'   =>  is_null($matchInfo['history']) ? [] : json_decode($matchInfo['history'],true),
        ];
        //处理tdk关键字
        $this->tdk->home_team_name = $analysis['info']['home_team_text'] ?? '';
        $this->tdk->away_team_name = $analysis['info']['away_team_text'] ?? '';
        $this->tdk->match_time = $analysis['info']['match_time'] ?? 0;
        $this->tdk->short_name_zh = $analysis['info']['competition_text'] ?? '';

        $model = new MatchVedio();
        $matchLive = $model->where(['match_id'=>$matchId])->find()->toArray();
        View::assign("matchLive",$matchLive);
        $this->getTempPath("jijin_zuqiu_detail");
        $this->getTdk('jijin_zuqiu_detail',$this->tdk);
        View::assign("index","集锦介绍");
    }
}