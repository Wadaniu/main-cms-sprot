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
            if($v['status_id']=='8'){
                array_unshift($footballDone,$v);
                unset($footballData[$k]);
            }
        }
        $basketballDone = [];
        foreach ($basketballData as $k=>$v){
            if($v['status_id']=='10'){
                array_unshift($basketballDone,$v);
                unset($basketballData[$k]);
            }
        }
     $matchData = array_merge($footballData,$footballDone,$basketballData,$basketballDone);

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
