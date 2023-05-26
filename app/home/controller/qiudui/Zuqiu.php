<?php

namespace app\home\controller\qiudui;

use app\home\BaseController;
use app\home\Tdk;
use think\App;
use think\facade\View;

class Zuqiu extends BaseController
{
    public function __construct(App $app)
    {
        parent::__construct($app);
        View::assign('type','zuqiu');
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
        $this->getTempPath('qiudui_zuqiu_detail');

        $this->getTdk('qiudui_zuqiu_detail',$this->tdk);
    }

    protected function getMatchList(string $compName)
    {
        $this->getTempPath('qiudui_zuqiu');

        $this->getTdk('qiudui_zuqiu',$this->tdk);
    }
}