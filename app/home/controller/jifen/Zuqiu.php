<?php

namespace app\home\controller\jifen;

use app\commonModel\FootballCompetition;
use app\home\BaseController;
use app\home\Tdk;
use think\App;
use think\facade\View;

class Zuqiu extends BaseController
{
    const RouteTag = 'jifen_zuqiu';

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->getTempPath(self::RouteTag);
    }

    public function index()
    {
        $param = $this->parmas;
        $defaultComp = getFootballHotComp(1);
        $compName = $param['compname'] ?? $defaultComp[0]['short_name_py'];

        //获取联赛
        $comp = FootballCompetition::getByPY($compName);
        if ($comp->isEmpty()) {
            abort(404, '参数错误');
        }

        $data = getCompTables(0, 0, $comp->id);

        $this->tdk = new Tdk();
        $this->tdk->short_name_zh = $comp->short_name_zh;
        $this->getTdk(self::RouteTag, $this->tdk);

        $list = count($data) > 0 ? $data[0] : false;
        View::assign('data', $list);
        return View::fetch($this->tempPath);
    }
}