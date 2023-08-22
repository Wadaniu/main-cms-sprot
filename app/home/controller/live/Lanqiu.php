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
use app\commonModel\BasketballTeam;

class Lanqiu extends BaseController
{
    public function __construct(App $app)
    {
        parent::__construct($app);
    }
    public function index(){
        $param = $this->parmas;
        //赛程id
        $compName = $param['compname'] ?? '';
        $matchId = $param['matchid'] ?? 0;

        $this->tdk = new Tdk();

        if (!empty($compName)){
            $count = BasketballCompetition::where('short_name_py',$compName)->count();
            if ($count <= 0){
                abort(404, '参数错误');
            }
        }
        if(!is_numeric($matchId)){
            abort(404, '参数错误');
        }

        if (empty($matchId)){
            $this->getMatchList($compName);
        }else{
            $this->getMatchInfo($matchId);
        }
        return View::fetch($this->tempPath);
    }

    protected function getMatchInfo($matchId)
    {
        $this->getTempPath('live_lanqiu_detail');
        $basketballMatchInfoModel = new BasketballMatchInfo();
        $matchInfo = $basketballMatchInfoModel->getByMatchId($matchId);
        //直播
        $model = new BasketballMatch();
        $matchLive = $model->findOrEmpty($matchId);

        if ($matchInfo->isEmpty() && $matchLive->isEmpty()){
            abort(404,'参数错误');
        }

        if ($matchLive->isEmpty()){
            $matchLive['mobile_link'] = [];
            $matchLive['pc_link'] = [];
        }else{
            $matchLive['mobile_link'] = json_decode($matchLive['mobile_link'],true);
            $matchLive['pc_link'] = json_decode($matchLive['pc_link'],true);
        }

        $matchLive = $matchLive->toArray();
        //历史交锋
        $analysis = [
            'info'      =>  is_null($matchInfo['info']) ? [] : json_decode($matchInfo['info'],true),
            'future'    =>  is_null($matchInfo['future']) ? [] : json_decode($matchInfo['future'],true),
            'history'   =>  is_null($matchInfo['history']) ? [] : json_decode($matchInfo['history'],true),
        ];
        //队伍统计
        $players = is_null($matchInfo['players']) ? [] : json_decode($matchInfo['players'],true);

        $teamModel = new BasketballTeam();
        $matchLive['home'] = $teamModel->getShortNameZhLogo($matchLive['home_team_id']);
        $matchLive['away'] = $teamModel->getShortNameZhLogo($matchLive['away_team_id']);
        $matchLive['comp'] = (new BasketballCompetition())->getShortNameZh($matchLive['competition_id']);
        $matchLive['home_scores'] = json_decode($matchLive['home_scores']);
        $matchLive['away_scores'] = json_decode($matchLive['away_scores']);


        //处理tdk关键字
        $this->tdk->home_team_name = $matchLive['home']['name_zh'] ?? '';
        $this->tdk->away_team_name = $matchLive['away']['name_zh'] ?? '';
        $this->tdk->match_time =  $matchLive['match_time'];
        $this->tdk->short_name_zh = $matchLive['comp']['short_name_zh'] ?? '';

        $this->getTdk('live_lanqiu_detail',$this->tdk);
        $matchLive['ball'] = 'lanqiu';
        //var_dump($analysis);die;
        View::assign("analysis",$analysis);
        View::assign("players",$players);
        View::assign("matchLive",$matchLive);
        View::assign("match",$matchLive);
        View::assign("comp",['id'=>$matchLive['competition_id']]);
    }

    protected function getMatchList(string $compName)
    {
        $this->getTempPath('live_lanqiu');

        $basketballModel = new BasketballMatch();
        if (empty($compName)){
            //篮球数据
            $data = $basketballModel->getCompetitionListInfo(0,50);
            $this->tdk->short_name_zh = '篮球';
        }else{
            //获取联赛id
            $comp = BasketballCompetition::getByPY($compName);
            //过滤联赛
            $data = $basketballModel->getCompetitionListInfo($comp['id'],50);
            //tdk关键字
            $this->tdk->short_name_zh = $comp['short_name_zh'];
            View::assign('comp',$comp);
        }

        $res = [];
        foreach ($data as $item){
            $res[date('Y-m-d',$item['match_time'])][] = $item;
        }

        $this->getTdk('live_lanqiu',$this->tdk);
        View::assign('data',$res);
    }

}