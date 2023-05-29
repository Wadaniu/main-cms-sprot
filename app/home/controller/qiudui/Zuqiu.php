<?php

namespace app\home\controller\qiudui;

use app\commonModel\FootballMatch;
use app\commonModel\FootballTeam;
use app\commonModel\MatchVedio;
use app\home\BaseController;
use app\home\Tdk;
use think\App;
use think\facade\View;

class Zuqiu extends BaseController
{
    const MainLimit = 5;
    public function __construct(App $app)
    {
        parent::__construct($app);
    }
    public function index(){
        $param = get_params();

        $teamid = $param['teamid'] ?? 0;

        $this->tdk = new Tdk();

        if ($teamid > 0){
            $this->getTeamInfo($teamid);
        }else{
            $this->getTeamList($param);
        }
        return View::fetch($this->tempPath);
    }

    protected function getTeamInfo($teamid)
    {
        $this->getTempPath('qiudui_zuqiu_detail');

        //队伍数据
        $team = FootballTeam::where('id',$teamid)->findOrEmpty();

        if ($team->isEmpty()) {
            $this->redirectTo(404);
        }

        //直播数据
        $matchModel = new FootballMatch();
        $matchList = $matchModel->getByTeam($teamid);

        $videoModel = new MatchVedio();
        $matchId = FootballMatch::whereRAW("home_team_id = :id OR away_team_id = :id",['id'=>$teamid])->column("id");
        //录像
        $luxiang = $videoModel->getByMatchId($matchId,1,self::MainLimit,2);
        //集锦
        $jijin = $videoModel->getByMatchId($matchId,1,self::MainLimit);
//
//        //资讯
//        $articleModel = new Article();
//        $article = $articleModel->getListByCompId(['competition_id'=>$compid],['limit'=>self::MainLimit]);

        $this->tdk->short_name_zh = $team->short_name_zh ?? '';
        $this->getTdk('qiudui_zuqiu_detail',$this->tdk);

        View::assign('data',$matchList);
        View::assign('luxiang',$luxiang);
        View::assign('jijin',$jijin);
//        View::assign('article',$article);
        View::assign('team',$team);
    }

    protected function getTeamList($param)
    {
        $this->getTempPath('qiudui_zuqiu');
        //赛程id
        $keyword = $param['keyword'] ?? '';

        if (empty($keyword)){
            $where = '1 = 1';
        }else{
            $where = [
                ['short_name_zh','like',$keyword.'%'],
                ['name_zh','like',$keyword.'%']
            ];
        }

        $param['limit'] = 24;
        //篮球数据
        $basketballModel = new FootballTeam();
        $data = $basketballModel->getList($where,$param)->toArray();

        $this->getTdk('qiudui_zuqiu',$this->tdk);
        View::assign('data',$data);
    }
}