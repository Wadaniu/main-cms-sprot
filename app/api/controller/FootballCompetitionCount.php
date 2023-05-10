<?php
declare (strict_types = 1);

namespace app\api\controller;

use app\admin\model\BasketballCompetition;
use app\admin\model\FootballCompetition;
use app\api\BaseController;
use app\commonModel\FootballCompetitionCount as FootballCompetitionCountModel;
use http\Env;
use think\Exception;
use think\facade\Request;

class FootballCompetitionCount extends BaseController
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::initialize();
        $this->model = new FootballCompetitionCountModel();
    }

    //比赛统计
    public function getCountById(){
        $id = Request::get('id',0);
        try {
            $data = $this->model->getFootballCompCountByCompId($id);
            $this->apiSuccess('请求成功',['list' => $data]);
        }catch (Exception $e){
            $this->apiError($e->getMessage());
        }

    }

    //射手榜
    public function getShootCountById(){
        $id = Request::get('id',0);
        $type = Request::get('type',0);
        try {
            $data = $this->model->getShootCountByCompId($id,$type);
            $this->apiSuccess('请求成功',['list' => $data]);
        }catch (Exception $e){
            $this->apiError($e->getMessage());
        }

    }


    //热门赛事
    public function getHotCompetition(){
        $type = Request::get('type', '');
        if (empty($type) || $type == "basketball") {
            $basketballCompetition = new  BasketballCompetition();
            $basketballHotData = $basketballCompetition->getHotData();
        }
        if (empty($type) || $type == "football") {
            $footballCompetition = new  FootballCompetition();
            $footballHotData = $footballCompetition->getHotData();
        }
        $data = array_merge($basketballHotData,$footballHotData);

        $this->apiSuccess('请求成功', ['list' => $data]);
    }

    //赛事简介
    public function info(){
        $id = Request::get('id', \think\facade\Env::get('Home.HOME_SPACE'));
        try {
            $footballCompetition = new  FootballCompetition();
            $data = $footballCompetition->info($id);
            $this->apiSuccess('请求成功',$data);
        }catch (Exception $e){
            $this->apiError($e->getMessage());
        }
    }
}