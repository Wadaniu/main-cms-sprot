<?php

namespace app\home\controller\live;

use app\commonModel\BasketballCompetition;
use app\commonModel\BasketballMatch;
use app\commonModel\FootballMatch;
use app\home\BaseController;
use app\home\Tdk;
use think\App;
use think\facade\View;

class Lanqiu extends BaseController
{
    private $tdk;
    public function __construct(App $app)
    {
        parent::__construct($app);
    }
    public function index(){
        $param = get_params();
        //赛程id
        $compName = $param['compname'] ?? '';
        $matchId = $param['matchid'] ?? 0;

        $this->tdk = new Tdk();

        if ($matchId > 0){
            $this->getMatchInfo($matchId);
        }else{
            $this->getMatchList($compName);
        }
        return View::fetch($this->tempPath);
    }

    protected function getMatchInfo($matchId)
    {
        $this->getTempPath('live_zuqiu_detail');

        //直播
        $model = new FootballMatch();
        $matchLive = $model->getMatchLive($matchId)->toArray();

        if ($matchLive){
            $matchLive['mobile_link'] = json_decode($matchLive['mobile_link']??'',true);
            $matchLive['pc_link'] = json_decode($matchLive['pc_link']??'',true);
        }
        $footballMatchInfoModel = new \app\commonModel\FootballMatchInfo();
        $matchInfo = $footballMatchInfoModel->getByMatchId($matchId);
        //历史交锋
        $analysis = [
            'info'      =>  is_null($matchInfo['info']) ? [] : json_decode($matchInfo['info'],true),
            'future'    =>  is_null($matchInfo['future']) ? [] : json_decode($matchInfo['future'],true),
            'history'   =>  is_null($matchInfo['history']) ? [] : json_decode($matchInfo['history'],true),
        ];
        //队伍统计
        $teamStats = is_null($matchInfo['team_stats']) ? [] : json_decode($matchInfo['team_stats'],true);

        //集锦/录像
        $matchVideoModel = new \app\commonModel\MatchVedio();
        $video = $matchVideoModel->getByMatchId($matchId,0);

        //处理tdk
        $this->getTdk('live_zuqiu_detail',$this->tdk);

        View::assign("analysis",$analysis);
        View::assign("teamStats",$teamStats);
        View::assign("video",$video);
        View::assign("matchLive",$matchLive);
    }

    protected function getMatchList(string $compName)
    {
        $this->getTempPath('live_lanqiu');

        $basketballModel = new BasketballMatch();
        if (empty($compName)){
            //篮球数据
            $data = $basketballModel->getWeekData();
            $this->tdk->short_name_zh = '篮球';
        }else{
            //获取联赛id
            $comp = BasketballCompetition::getByName($compName);
            //过滤联赛
            $data = $basketballModel->getWeekData([$comp['id']]);
            //tdk关键字
            $this->tdk->short_name_zh = $comp['short_name_zh'];
        }

        $this->getTdk('live_lanqiu',$this->tdk);
        View::assign('data',$data);
    }

}