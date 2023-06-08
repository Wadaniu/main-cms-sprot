<?php

namespace app\home\controller\qiudui;

use app\commonModel\BasketballTeam;
use app\commonModel\FootballTeam;
use app\home\BaseController;
use app\home\Tdk;
use think\App;
use think\facade\View;

class Index extends BaseController
{
    const RouteTag  = 'qiudui';

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->getTempPath(self::RouteTag);
    }
    public function index(){
        $param = $this->parmas;

        $keyword = $param['keyword'] ?? '';
        $param['page'] = $param['page'] ?? 1;
        $param['limit'] = 12;
        //每页12条篮球和足球联赛数据
        $footballModel = new FootballTeam();
        $footballData = $footballModel->getList($keyword,$param);
        //篮球数据
        $basketballModel = new BasketballTeam();
        $basketballData = $basketballModel->getList($keyword,$param);

        $footballData['per_page'] = 24;
        $footballData['current_page'] = $param['page'];
        $footballData['data'] = array_merge($footballData["data"],$basketballData["data"]);
        //处理tdk
        $tdk = new Tdk();
        $this->getTdk(self::RouteTag,$tdk);

        View::assign('data',$footballData);
        return View::fetch($this->tempPath);
    }
}