<?php
declare (strict_types=1);

namespace app\api\controller;

use app\admin\model\BasketballCompetition;
use app\admin\model\BasketballMatch;
use app\admin\model\FootballCompetition;
use app\admin\model\FootballMatch;
use app\api\BaseController;
use app\commonModel\FootballMatchCount;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\facade\Request;

class FootballCompetitionMatch extends BaseController
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::initialize();
        $this->model = new FootballMatchCount();
    }

    /**
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    private function voList($basketballIds, $footballIds, $type, $dataTime){
        $basketballMatch = new BasketballMatch();
        $footballMatch = new FootballMatch();
        $basketball = array();
        $football = array();
        if($type=="basketball") {
            $basketball = $basketballMatch->getMathchByDate($basketballIds,$dataTime);
        }
        else if($type=="football") {
            $football = $footballMatch->getBeWeekData($footballIds,$dataTime);
        }
        else{
            $football = $footballMatch->getBeWeekData($footballIds,$dataTime);
            $basketball = $basketballMatch->getMathchByDate($basketballIds,$dataTime);
        }

        $voList = array_merge($basketball,$football);
        return $voList;
    }

    public function index()
    {
        $type = Request::get('type', '');
        $id = Request::get('id', 0);
        $dateTime = Request::get('date_time', date('Y-m-d', time()));

        try {
            $basketballIds = array();
            $footballIds = array();

            //获取热门赛事
            if (empty($type) || $type == "basketball") {
                $basketballCompetition = new  BasketballCompetition();
                $basketballHotData = $basketballCompetition->getHotData();
                if (empty($id)) {
                    foreach ($basketballHotData as $vo) {
                        $basketballIds[] = $vo["id"];
                    }
                } else {
                    $basketballIds[] = $id;
                }
            }
            if (empty($type) || $type == "football") {
                $footballCompetition = new  FootballCompetition();
                $footballHotData = $footballCompetition->getHotData();
                if (empty($id)) {
                    foreach ($footballHotData as $vo) {
                        $footballIds[] = $vo["id"];
                    }
                } else {
                    $footballIds[] = $id;
                }
            }

            $voList = $this->voList($basketballIds, $footballIds, $type, $dateTime);
            $this->apiSuccess('请求成功', ['list' => $voList]);
        } catch (Exception $e) {
            $this->apiError($e->getMessage());
        }
    }

    //比赛记录历史
    public function getMatchAnalysis(){
        $id = Request::get('id', 0);
        try {
            $data = $this->model->getMatchAnalysis($id);
            $this->apiSuccess('请求成功', ['list' => $data]);
        }catch (Exception $e){
            $this->apiError($e->getMessage());
        }
    }

    //比赛队伍统计
    public function getMatchTeamStats(){
        $id = Request::get('id', 0);
        try {
            $data = $this->model->getMatchTeamStats($id);
            if (empty($data)){
                $data = [];
            }
            $this->apiSuccess('请求成功', ['list' => $data]);
        }catch (Exception $e){
            $this->apiError($e->getMessage());
        }
    }

    //比赛集锦录像
    public function getMatchVideoCollection(){
        $id = Request::get('id', 0);
        try {
            $data = $this->model->getMatchVideoCollection($id);
            $this->apiSuccess('请求成功', ['list' => $data]);
        }catch (Exception $e){
            $this->apiError($e->getMessage());
        }
    }

    //比赛直播地址
    public function getMatchLive(){
        $id = Request::get('id', 0);
        try {
            $model = new FootballMatch();
            $data = $model->getMatchLive($id);
            if ($data){
                $data->mobile_link = json_decode($data->mobile_link??'');
                $data->pc_link = json_decode($data->pc_link??'');
            }
            $this->apiSuccess('请求成功', $data);
        }catch (Exception $e){
            $this->apiError($e->getMessage());
        }
    }

    //获取首页比赛
    public function getFootballMatchIndex(){
        try {
            $footballCompetition = new  FootballCompetition();
            $footballHotData = $footballCompetition->getHotData();
            $hotIds = array_column($footballHotData,'id');
            $model = new FootballMatch();
            $data = $model->getFootballMatchIndex($hotIds);
            $this->apiSuccess('请求成功', ['list' => $data]);
        }catch (Exception $e){
            $this->apiError($e->getMessage());
        }
    }

    public function getByTeam(){
        $id = Request::get('id', 0);
        try {
            $model = new FootballMatch();
            $data = $model->getByTeam($id);
            $this->apiSuccess('请求成功', ['list' => $data]);
        }catch (Exception $e){
            $this->apiError($e->getMessage());
        }
    }
}
