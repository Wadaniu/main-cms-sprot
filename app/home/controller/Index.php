<?php
/**
 * @copyright Copyright (c) 2021 勾股工作室
 * @license https://opensource.org/licenses/Apache-2.0
 * @link https://www.gougucms.com
 */

declare (strict_types = 1);

namespace app\home\controller;

use app\commonModel\BasketballMatch;
use app\commonModel\FootballMatch;
use app\home\BaseController;
use app\home\Tdk;
use think\App;
use think\facade\Cache;
use think\facade\Env;
use think\facade\View;

class Index extends BaseController
{
    const RouteTag  = 'index';

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
        $footballData = $footballModel->getTodayData($hotFootballCompId);
        //篮球数据
        $basketballModel = new BasketballMatch();
        $basketballData = $basketballModel->getTodayData($hotBasketballCompId);

        $footballDone = [];
        foreach ($footballData as $k=>$v){
            
        }


        $startTime = strtotime(date('Y-m-d',time()).' 00:00:00');
        $endTime = strtotime(date('Y-m-d',time()).' 23:59:59');

        //已完结的要倒序，未开赛和直播中要正序
        $footballData1 = $footballModel->getMatchInfo([["match_time","between",[$startTime,$endTime]],['status_id','IN',[1,2,3,4,5,7]]],$hotFootballCompId,50,$order="status_id desc,match_time asc");
        $footballDone = $footballModel->getMatchInfo([["match_time","between",[$startTime,$endTime]],['status_id','IN',[8]]],$hotFootballCompId,50,$order="status_id desc,match_time desc");


        $basketballData1 = $basketballModel->getMatchInfo([["match_time","between",[$startTime,$endTime]],['status_id','IN',[1,2,3,4,5,6,7,8,9]]],$hotBasketballCompId,50,$order="status_id desc,match_time asc");
        $basketballDone = $basketballModel->getMatchInfo([["match_time","between",[$startTime,$endTime]],['status_id','IN',[10]]],$hotBasketballCompId,50,$order="status_id desc,match_time desc");


        $matchData = array_merge($footballData1,$footballDone,$basketballData1,$basketballDone);
        //array_multisort(array_column($matchData,'match_time'),SORT_ASC,$matchData);

        $res = [];
        foreach ($matchData as $item){
            $res[date('Y-m-d',$item['match_time'])][] = $item;
        }

        //处理tdk
        $tdk = new Tdk();
        $this->getTdk(self::RouteTag,$tdk);
        //var_dump($res);die;
        View::assign('data',$res);
        return View::fetch($this->tempPath);
    }

}
