<?php

namespace app\home\controller\liansai;

use app\commonModel\BasketballCompetition;
use app\commonModel\FootballCompetition;
use app\home\BaseController;
use app\home\Tdk;
use think\App;
use think\facade\View;

class Index extends BaseController
{
    const RouteTag  = 'liansai';

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->getTempPath(self::RouteTag);
    }
    public function index(){

        //每页五条篮球和足球联赛数据
        $footballModel = new FootballCompetition();
        $footballData = $footballModel->getList('1=1',['limit'=>5,'page'=>1]);
        var_dump($footballData);die;
        //篮球数据
        $basketballModel = new BasketballCompetition();
        $basketballData = $basketballModel->getWeekData();

        $matchData = array_merge($footballData,$basketballData);

        $res = [];
        foreach ($matchData as $item){
            $res[date('Y-m-d',$item['match_time'])][] = $item;
        }

        //处理tdk
        $tdk = new Tdk();
        $this->getTdk(self::RouteTag,$tdk);

        View::assign('data',$res);
        return View::fetch($this->tempPath);
    }
}