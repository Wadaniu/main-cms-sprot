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
        $param = get_params();

        $keyword = $param['keyword'] ?? '';

        if (empty($keyword)){
            $where = '1 = 1';
        }else{
            $where = [
                ['short_name_zh','like',$keyword.'%'],
                ['name_zh','like',$keyword.'%']
            ];
        }

        //每页12条篮球和足球联赛数据
        $footballModel = new FootballTeam();
        $footballData = $footballModel->getList($where,['limit'=>12])->toArray();
        //篮球数据
        $basketballModel = new BasketballTeam();
        $basketballData = $basketballModel->getList($where,['limit'=>12])->toArray();

        $footballData['per_page'] = 24;
        $footballData['data'] = array_merge($footballData["data"],$basketballData["data"]);
        //处理tdk
        $tdk = new Tdk();
        $this->getTdk(self::RouteTag,$tdk);

        View::assign('data',$footballData);
        return View::fetch($this->tempPath);
    }
}