<?php

namespace app\home\controller\yuce;

use app\home\BaseController;
use app\home\Tdk;
use think\App;
use think\facade\View;
use app\commonModel\Article;
use app\commonModel\BasketballCompetition;
use app\commonModel\Admin;
use app\commonModel\ArticleKeywords;
use app\commonModel\BasketballMatch;
use app\commonModel\BasketballMatchInfo;

class Lanqiu extends BaseController
{
    public function __construct(App $app)
    {
        parent::__construct($app);
    }
    public function index(){
        $param = $this->parmas;

        $teamid = $param['aid'] ?? 0;

        $this->tdk = new Tdk();

        if (!empty($teamid)){
            $this->getCompInfo($teamid);
        }else{
            $this->getMatchList($param);
        }
        return View::fetch($this->tempPath);
    }

    protected function getCompInfo($matchId)
    {
        if(!is_numeric($matchId)){
            throw new \think\exception\HttpException(404, '找不到页面');
        }
        $comp = BasketballMatch::where('id',$matchId)->findOrEmpty();
        if ($comp->isEmpty()) {
            throw new \think\exception\HttpException(404, '找不到页面');
        }
        $basketballMatchInfoModel = new BasketballMatchInfo();
        $matchInfo = $basketballMatchInfoModel->getByMatchId($matchId);
        //直播
        $model = new BasketballMatch();
        $matchLive = $model->getMatchLive($matchId);

        if ($matchLive->isEmpty()){
            $matchLive['mobile_link'] = [];
            $matchLive['pc_link'] = [];
        }else{
            $matchLive['mobile_link'] = json_decode($matchLive['mobile_link'],true);
            $matchLive['pc_link'] = json_decode($matchLive['pc_link'],true);
        }

        //历史交锋
        $analysis = [
            'info'      =>  is_null($matchInfo['info']) ? [] : json_decode($matchInfo['info'],true),
            'future'    =>  is_null($matchInfo['future']) ? [] : json_decode($matchInfo['future'],true),
            'history'   =>  is_null($matchInfo['history']) ? [] : json_decode($matchInfo['history'],true),
        ];
        //队伍统计
        $players = is_null($matchInfo['players']) ? [] : json_decode($matchInfo['players'],true);


        $this->getTempPath('yuce_lanqiu_detail');
        $info = $comp->toArray();
        $comp = BasketballCompetition::where("id",$info["competition_id"])->find()->toArray();
        $footballTeam = new \app\commonModel\BasketballTeam();
        $home = $footballTeam->getShortNameZhLogo($info["home_team_id"]);
        $away = $footballTeam->getShortNameZhLogo($info["away_team_id"]);
        $this->tdk->home_team_name = $home['name_zh']??'';
        $this->tdk->away_team_name = $away['name_zh']??'';
        $this->tdk->match_time = $info['match_time'];
        $info['home'] = $home;
        $info['away'] = $away;
        $this->getTdk('yuce_zuqiu_detail',$this->tdk);
        View::assign("info",$info);
        View::assign("comp",$comp);
        View::assign("analysis",$analysis);
        View::assign("ball",'lanqiu');
    }

    protected function getMatchList($param)
    {
        $param = $this->parmas;
        $param['order'] = "match_time asc";
        $param['page'] = isset($param['page'])?$param['page']:1;
        $param['limit'] = 10;

        $this->tdk->short_name_zh = '篮球';
        $model = (new BasketballMatch());
        $where = [
            ["match_time",">=",time()],
            ["forecast","NOT NULL","NOT NULL"]
        ];
        $list = $model->getBasketballMatchList2($where,$param);
        $footballTeam = new \app\commonModel\BasketballTeam();
        $comp = new BasketballCompetition();
        foreach ($list['data'] as $k=>$v){
            $list["data"][$k]['home'] = [];
            $list["data"][$k]['away'] = [];
            $home = $footballTeam->getShortNameZhLogo($v["home_team_id"]);
            $list["data"][$k]['home'] = $home;
            $away = $footballTeam->getShortNameZhLogo($v["away_team_id"]);
            $list["data"][$k]['away'] = $away;
            $list['data'][$k]['comp'] = $comp->getShortNameZh($v['competition_id']);
        }
        $list['current_page'] = $param['page'];
        $this->getTdk('yuce_lanqiu',$this->tdk);
        View::assign("list",$list);
        View::assign('param',$param);
        View::assign("ball",'lanqiu');
        $this->getTempPath('yuce_zuqiu');
        //var_dump($list);die;
    }
}