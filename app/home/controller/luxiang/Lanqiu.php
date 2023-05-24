<?php

namespace app\home\controller\luxiang;

use app\home\BaseController;
use think\facade\View;
use think\App;
use app\home\Tdk;
use app\commonModel\MatchVedio;

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
        $compName = $param['compname'] ?? '';
        $matchId = $param['matchid'] ?? 0;

        $this->tdk = new Tdk();

        if ($matchId > 0){
            $this->getMatchInfo($matchId);
        }else{
            $this->getMatchList($compName);
        }
        return View::fetch($this->tempPath);

    }


    function getMatchList(){
        $this->getTempPath('luxiang_lanqiu');
        $this->getTdk('luxiang_lanqiu',$this->tdk);
        $list = (new MatchVedio())->getList(['type'=>2,'video_type'=>1],["order"=>'match_id desc']);
        foreach ($list['data'] as $k=>$v){
            $list['data'][$k]['date']='';
            $list['data'][$k]['team']=[];
            $titleArr = explode(" ",$v['title']);
            if(preg_match("/月/",$titleArr[1])){
                $list['data'][$k]['date'] = str_replace("日","",str_replace("月","-",$titleArr[1]));
            }
            $list['data'][$k]['team'] = explode("vs",$titleArr[3]);
        }
        View::assign("list",$list);
        View::assign("index","录像");
    }

    function getMatchInfo($matchId){

        $model = new MatchVedio();
        $matchLive = $model->where(['match_id'=>$matchId])->find()->toArray();
        View::assign("matchLive",$matchLive);
        $this->getTempPath("luxiang_lanqiu_detail");
        $this->getTdk('luxiang_lanqiu_detail',$this->tdk);
        View::assign("index","录像介绍");
    }
}