<?php

namespace app\home\controller\liansai;

use app\commonModel\BasketballCompetition;
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

        $compid = $param['compid'] ?? 0;

        $this->tdk = new Tdk();

        if ($compid > 0){
            $this->getCompInfo($compid);
        }else{
            $this->getMatchList($param);
        }
        return View::fetch($this->tempPath);
    }

    protected function getCompInfo($matchId)
    {
        $this->getTempPath('live_zuqiu_detail');



        $this->getTdk('live_zuqiu_detail',$this->tdk);
    }

    protected function getMatchList(string $compName)
    {
        $this->getTempPath('liansai_lanqiu');

        //篮球数据
        $basketballModel = new BasketballCompetition();
        $basketballData = $basketballModel->getList('1 = 1',['limit'=>5])->toArray();

        $this->getTdk('liansai_lanqiu',$this->tdk);
        View::assign('data',$basketballData);
    }
}