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
        $this->getTempPath('yuce_lanqiu_detail');
        $info = $comp->toArray();
        $comp = BasketballCompetition::where("id",$info["competition_id"])->find()->toArray();
        $footballTeam = new \app\commonModel\BasketballTeam();
        $home = $footballTeam->getShortNameZhLogo($info["home_team_id"]);
        $away = $footballTeam->getShortNameZhLogo($info["away_team_id"]);
        $this->tdk->home_team_name = $home['name_zh']??'';
        $this->tdk->away_team_name = $away['name_zh']??'';
        $this->tdk->match_time = date("Y-m-d H:i");
        $info['home'] = $home;
        $info['away'] = $away;
        $this->getTdk('yuce_zuqiu_detail',$this->tdk);
        View::assign("info",$info);
        View::assign("comp",$comp);
        View::assign("ball",'zuqiu');
    }

    protected function getMatchList($param)
    {
        $param = $this->parmas;

        $param['page'] = isset($param['page'])?$param['page']:1;
        $param['limit'] = 10;
        $this->tdk->short_name_zh = '篮球';
        $model = (new BasketballMatch());
        $where = [
            ["match_time",">=",time()],
            ["forecast","NOT NULL","NOT NULL"]
        ];
        if(isset($param['compname']) && $param['compname']){
            $competition = BasketballCompetition::where("short_name_py",$param['compname'])->find();
            if($competition){
                $where[] = ["competition_id","in",[$competition->id]];
            }
            View::assign('comp',$competition);
        }
        $list = $model->getBasketballMatchList($where,["order"=>"match_time asc"])->toArray();
        $footballTeam = new \app\commonModel\BasketballTeam();
        foreach ($list['data'] as $k=>$v){
            $list["data"][$k]['home'] = [];
            $list["data"][$k]['away'] = [];
            $str = "主队vs客队:预测分析,比分预测,在线直播,比赛结果";
            $home = $footballTeam->getShortNameZhLogo($v["home_team_id"]);
            $list["data"][$k]['home'] = $home;
            $away = $footballTeam->getShortNameZhLogo($v["away_team_id"]);
            $list["data"][$k]['away'] = $away;
        }
        $this->getTdk('yuce_lanqiu',$this->tdk);
        View::assign("list",$list);
        View::assign('param',$param);
        View::assign("ball",'lanqiu');
        $this->getTempPath('yuce_zuqiu');
    }
}