<?php

namespace app\home\controller\zixun;

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

        $teamid = $param['aid'] ?? 0;

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
        $this->getTempPath('zixun_zuqiu_detail');

        $this->getTdk('zixun_zuqiu_detail',$this->tdk);
    }

    protected function getMatchList(string $compName)
    {
        $this->getTempPath('zixun_zuqiu');

        $this->getTdk('zixun_zuqiu',$this->tdk);
    }
}