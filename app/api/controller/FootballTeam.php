<?php

namespace app\api\controller;
use app\api\BaseController;
use app\commonModel\FootballCompetition;
use app\commonModel\FootballTeam as FootballTeamModel;
use think\Exception;
use think\facade\Request;

class FootballTeam extends BaseController
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::initialize();
        $this->model = new FootballTeamModel();
    }

    public function info(){
        $id = Request::get('id', 0);
        try {
            $data = $this->model->getFootballTeamById($id);
            $cmpModel = new FootballCompetition();
            $cmpInfo = $cmpModel->info($data->competition_id);
            $data->competition_text = $cmpInfo->name_zh ?? '';
            $data->competition_short_text = $cmpInfo->short_name_zh ?? '';
//            $this->apiSuccess('请求成功', $data);
        }catch (Exception $e){
            $this->apiError($e->getMessage());
        }
    }
}