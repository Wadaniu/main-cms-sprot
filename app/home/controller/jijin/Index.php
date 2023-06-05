<?php

namespace app\home\controller\jijin;

use app\home\BaseController;
use think\App;
use think\facade\View;
use app\home\Tdk;
use app\commonModel\MatchVedio;
use app\commonModel\FootballTeam;
use app\commonModel\BasketballTeam;

class Index extends BaseController
{
    const RouteTag  = 'jijin';

    public function __construct(App $app)
    {
        parent::__construct($app);
    }

    public function index(){
        $param = get_params();
        $tdk = new Tdk();
        $this->getTdk(self::RouteTag,$tdk);
        $this->getTempPath(self::RouteTag);
        $param['page'] = isset($param['page'])?$param['page']:1;
        $model = new MatchVedio();
        $list = $model->getList(['type'=>1],$param)->toArray();
        $footballTeam = new FootballTeam();
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
                        if($v['video_type']=='0'){
                            $teamArr[] = ['name'=>$t,'id'=>$footballTeam->getTeamInfoByName($t,'name_zh')];
                        }else{
                            $teamArr[] = ['name'=>$t,'id'=>$basketballTeam->getTeamInfoByName($t,'name_zh')];
                        }
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
        View::assign("short",[]);
        View::assign("href","");
        View::assign("compName",'');
        View::assign("list",$list);
        View::assign("index","集锦");
        View::assign("compName",'');
        View::assign("param",$param);
        View::assign("luxiang",getLuxiangJijin(2,'',0,4));
        return View::fetch($this->tempPath);

    }
}