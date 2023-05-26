<?php

namespace app\home\controller\liansai;

use app\commonModel\BasketballCompetition;
use app\commonModel\BasketballMatch;
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
            $this->getCompList($param);
        }
        return View::fetch($this->tempPath);
    }

    protected function getCompInfo($compid)
    {
        $this->getTempPath('liansai_lanqiu_detail');

        $matchModel = new BasketballMatch();
        $match = $matchModel->getMatchInfo(['status_id','IN',[1,2,3,4,5,7,8,9]],[$compid],5);


        $this->getTdk('liansai_lanqiu_detail',$this->tdk);
    }

    protected function getCompList($param)
    {
        $this->getTempPath('liansai_lanqiu');
        //赛程id
        $keyword = $param['keyword'] ?? '';

        if (empty($keyword)){
            $where = '1 = 1';
        }else{
            $where = ['short_name_zh','like',$keyword.'%'];
        }
        $param['limit'] = 40;
        //篮球数据
        $basketballModel = new BasketballCompetition();
        $basketballData = $basketballModel->getList($where,$param)->toArray();

        $this->getTdk('liansai_lanqiu',$this->tdk);
        View::assign('data',$basketballData);
    }
}