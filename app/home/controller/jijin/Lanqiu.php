<?php

namespace app\home\controller\jijin;

use app\home\BaseController;
use think\facade\View;
use think\App;
use app\home\Tdk;
use app\commonModel\MatchVedio;

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


    function getMatchList(){
        $this->getTempPath('jijin_lanqiu');
        $this->getTdk('jijin_lanqiu',$this->tdk);
        $list = (new MatchVedio())->getList(['type'=>1,'video_type'=>1],["order"=>'match_id desc']);
        foreach ($list['data'] as $k=>$v){
            $list['data'][$k]['date']='';
            $list['data'][$k]['team']=[];
            $titleArr = explode(" ",$v['title']);
            if(preg_match("/月/",$titleArr[1])){
                $list['data'][$k]['date'] = str_replace("日","",str_replace("月","-",$titleArr[1]));
            }
            if(isset($titleArr[3])){
                $list['data'][$k]['team'] = explode("vs",$titleArr[3]);
            }
        }
        View::assign("list",$list);
        View::assign("index","集锦");
    }
    function getMatchInfo($matchId){
        $model = new MatchVedio();
        $matchLive = $model->where(['match_id'=>$matchId])->find()->toArray();
        View::assign("matchLive",$matchLive);
        $this->getTempPath("jijin_lanqiu_detail");
        $this->getTdk('jijin_lanqiu_detail',$this->tdk);
        View::assign("index","集锦介绍");
    }



}