<?php

namespace app\home\controller\live;

use app\commonModel\BasketballMatch;
use app\commonModel\FootballMatch;
use app\home\BaseController;
use app\home\Tdk;
use think\App;
use think\facade\View;

class Index extends BaseController
{
    const RouteTag  = 'live';

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->getTempPath(self::RouteTag);
    }
    public function index(){
        $basketballComp = getBasketballHotComp();
        $footballComp = getFootballHotComp();

        $hotFootballCompId = array_column($footballComp,'id');
        $hotBasketballCompId = array_column($basketballComp,'id');

        //获取热门联赛一周内数据
        //足球数据
        $footballModel = new FootballMatch();
        $footballData = $footballModel->getWeekData($hotFootballCompId);
        //篮球数据
        $basketballModel = new BasketballMatch();
        $basketballData = $basketballModel->getWeekData($hotBasketballCompId);

        $matchData = array_merge($footballData,$basketballData);

        $res = [];
        foreach ($matchData as $item){
            $res[date('Y-m-d',$item['match_time'])][] = $item;
        }

        //处理tdk
        $tdk = new Tdk();
        $this->getTdk(self::RouteTag,$tdk);
        View::assign('data',$res);
        View::assign('article',['data'=>getZiXun()]);
        return View::fetch($this->tempPath);

    }
}