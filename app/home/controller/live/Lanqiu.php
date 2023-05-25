<?php

namespace app\home\controller\live;

use app\commonModel\BasketballCompetition;
use app\commonModel\BasketballMatch;
use app\commonModel\BasketballMatchInfo;
use app\commonModel\MatchVedio;
use app\home\BaseController;
use app\home\Tdk;
use think\App;
use think\facade\View;

class Lanqiu extends BaseController
{
    public function __construct(App $app)
    {
        parent::__construct($app);
        View::assign('type','basketball');
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
        $this->getTempPath('live_lanqiu_detail');

        //直播
        $model = new BasketballMatch();
        $matchLive = $model->getMatchLive($matchId);

        if ($matchLive){
            $matchLive['mobile_link'] = json_decode($matchLive['mobile_link']??'',true);
            $matchLive['pc_link'] = json_decode($matchLive['pc_link']??'',true);
        }

        $basketballMatchInfoModel = new BasketballMatchInfo();
        $matchInfo = $basketballMatchInfoModel->getByMatchId($matchId);
        //历史交锋
        $analysis = [
            'info'      =>  is_null($matchInfo['info']) ? [] : json_decode($matchInfo['info'],true),
            'future'    =>  is_null($matchInfo['future']) ? [] : json_decode($matchInfo['future'],true),
            'history'   =>  is_null($matchInfo['history']) ? [] : json_decode($matchInfo['history'],true),
        ];
        //队伍统计
        $players = is_null($matchInfo['players']) ? [] : json_decode($matchInfo['players'],true);

        //集锦/录像
        $matchVideoModel = new MatchVedio();
        $video = $matchVideoModel->getByMatchId($matchId,1);

        //处理tdk关键字
        $this->tdk->home_team_name = $analysis['info']['home_team_text'] ?? '';
        $this->tdk->away_team_name = $analysis['info']['away_team_text'] ?? '';
        $this->tdk->match_time = $analysis['info']['match_time'] ?? 0;
        $this->tdk->short_name_zh = $analysis['info']['competition_text'] ?? '';

        $this->getTdk('live_lanqiu_detail',$this->tdk);

        View::assign("analysis",$analysis);
        View::assign("players",$players);
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
            $comp = BasketballCompetition::getByPY($compName);
            //过滤联赛
            $data = $basketballModel->getWeekData([$comp['id']]);
            //tdk关键字
            $this->tdk->short_name_zh = $comp['short_name_zh'];
        }

        $res = [];
        foreach ($data as $item){
            $res[date('Y-m-d',$item['match_time'])][] = $item;
        }

        $this->getTdk('live_lanqiu',$this->tdk);
        View::assign('data',$res);
    }

}