<?php

namespace app\home\controller\qiudui;

use app\home\BaseController;
use app\home\Tdk;
use think\App;
use think\facade\View;

class Lanqiu extends BaseController
{
    public function __construct(App $app)
    {
        parent::__construct($app);
        View::assign('type','lanqiu');
    }
    public function index(){
        $param = get_params();

        $teamid = $param['teamid'] ?? 0;

        $this->tdk = new Tdk();

        if ($teamid > 0){
            $this->getCompInfo($teamid);
        }else{
            $this->getMatchList($param);
        }
        return View::fetch($this->tempPath);
    }

    protected function getCompInfo($matchId)
    {
        $this->getTempPath('qiudui_lanqiu_detail');

        $this->getTdk('qiudui_lanqiu_detail',$this->tdk);
    }

    protected function getMatchList(string $compName)
    {
        $this->getTempPath('qiudui_lanqiu');

        $this->getTdk('qiudui_lanqiu',$this->tdk);
    }
}