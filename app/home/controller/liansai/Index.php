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
        $param = get_params();
        //赛程id
        $keyword = $param['keyword'] ?? '';

        if (empty($keyword)){
            $where = '1 = 1';
        }else{
            $where = ['short_name_zh','like',$keyword.'%'];
        }
        //每页五条篮球和足球联赛数据
        $footballModel = new FootballCompetition();
        $footballData = $footballModel->getList($where,['limit'=>5])->toArray();
        //篮球数据
        $basketballModel = new BasketballCompetition();
        $basketballData = $basketballModel->getList($where,['limit'=>5])->toArray();

        $footballData['data'] = array_merge($footballData["data"],$basketballData["data"]);
        //处理tdk
        $tdk = new Tdk();
        $this->getTdk(self::RouteTag,$tdk);

        View::assign('data',$footballData);
        return View::fetch($this->tempPath);
    }
}