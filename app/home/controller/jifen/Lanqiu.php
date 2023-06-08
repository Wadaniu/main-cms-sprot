<?php

namespace app\home\controller\jifen;

use app\commonModel\BasketballCompetition;
use app\home\BaseController;
use app\home\Tdk;
use think\App;
use think\facade\View;

class Lanqiu extends BaseController
{
    const RouteTag  = 'jifen_lanqiu';
    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->getTempPath(self::RouteTag);
    }

    public function index(){
        $param = $this->parmas;
        $compName = $param['compname'];

        //获取联赛
        $comp = BasketballCompetition::getByPY($compName);
        if ($comp->isEmpty()){
            abort(404, '参数错误');
        }

        $data = getCompTables(0,1,$comp->id);

        $this->tdk = new Tdk();
        $this->tdk->short_name_zh = $comp->short_name_zh;
        $this->getTdk(self::RouteTag,$this->tdk);
        View::assign('data',$data);
        return View::fetch($this->tempPath);
    }

}