<?php

namespace app\home\controller\luxiang;

use app\home\BaseController;
use think\facade\View;
use think\App;
use app\home\Tdk;
use app\commonModel\MatchVedio;
use app\commonModel\FootballTeam;
use app\commonModel\BasketballTeam;

class Index extends BaseController
{

    const RouteTag  = 'luxiang';

    public function __construct(App $app)
    {
        parent::__construct($app);
    }

    public function index(){
        //处理tdk
        $tdk = new Tdk();
        $this->getTdk(self::RouteTag,$tdk);
        $this->getTempPath(self::RouteTag);



//        $param['page'] = isset($param['page'])?$param['page']:1;
//        $model = new MatchVedio();
//        $list = $model->getList(['type'=>2],$param)->toArray();
//        $footballTeam = new FootballTeam();
//        $basketballTeam = new BasketballTeam();
//        foreach ($list['data'] as $k=>$v){
//            $list['data'][$k]['date']='';
//            $list['data'][$k]['short_name_py']='';
//            $titleArr = explode(" ",$v['title']);
//            $list['data'][$k]['teamArr'] = [];
//            $team = explode("vs",$titleArr[3]);
//            if($team){
//                $teamArr = [];
//                foreach ($team  as $t){
//                    if($v['video_type']=='0'){
//                        $teamArr[] = ['name'=>$t,'id'=>$footballTeam->getTeamInfoByName($t,'name_zh')];
//                    }else{
//                        $teamArr[] = ['name'=>$t,'id'=>$basketballTeam->getTeamInfoByName($t,'name_zh')];
//                    }
//                }
//                $list['data'][$k]['teamArr'] = $teamArr;
//            }
//            $competition = $model->getCompetitionInfo($v['id']);
//            if(isset($competition['match']['match_time'])){
//                $list['data'][$k]['date'] = date('m-d',$competition['match']['match_time']);
//            }
//            $list['data'][$k]['short_name_py'] = empty($competition['competition'])?($v['video_type']=='0'?'zuqiu':'lanqiu'):$competition['competition']['short_name_py'];
//        }
        list($list,$competition_id,$param)=getMatchVedio(['type'=>2]);

        View::assign("short",[]);
        View::assign("href","/luxiang/zuqiu/");
        View::assign("list",$list);
        View::assign("index","录像");
        View::assign("compName",'');
        View::assign("param",$param);
        View::assign("jijin",getLuxiangJijin(1,'',$competition_id));
        return View::fetch($this->tempPath);
    }

}