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
            $where = [
                ['name_zh','like','%'.$keyword.'%']
            ];
        }
        $param['limit'] = 12;
        //每页12条篮球和足球联赛数据
        $footballModel = new FootballCompetition();
        $footballData = $footballModel->getList($where,$param)->toArray();
        //篮球数据
        $basketballModel = new BasketballCompetition();
        $basketballData = $basketballModel->getList($where,$param)->toArray();

        $footballData['per_page'] = 24;
        $footballData['data'] = array_merge($footballData["data"],$basketballData["data"]);
        //处理tdk
        $tdk = new Tdk();
        $this->getTdk(self::RouteTag,$tdk);

        View::assign('data',$footballData);
        return View::fetch($this->tempPath);
    }
}