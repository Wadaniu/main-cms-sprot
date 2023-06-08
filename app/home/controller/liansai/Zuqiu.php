<?php

namespace app\home\controller\liansai;

use app\commonModel\Article;
use app\commonModel\FootballCompetition;
use app\commonModel\FootballMatch;
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
        $param = $this->parmas;

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
        $this->getTempPath('liansai_zuqiu_detail');

        //联赛数据
        $comp = FootballCompetition::where('id',$compid)->findOrEmpty();
        if ($comp->isEmpty()) {
            $this->redirectTo(404);
        }

        //直播数据
        $matchModel = new FootballMatch();
        $matchList = $matchModel->getMatchInfo([['status_id','IN',[1,2,3,4,5,7]]],[$compid],self::MainLimit,"status_id asc,match_time asc");
        if (empty($matchList)){
            $matchList = $matchModel->getMatchInfo([['status_id',8]],[$compid],self::MainLimit,'match_time desc');
        }

        $videoModel = new MatchVedio();
        $matchId = FootballMatch::where("competition_id",$compid)->column("id");
        //录像
        $luxiang = $videoModel->getByMatchId($matchId,0,self::MainLimit,2);
        //集锦
        $jijin = $videoModel->getByMatchId($matchId,0,self::MainLimit);

        //资讯
        $articleModel = new Article();
        $article = $articleModel->getListByCompId(0,['competition_id'=>$compid],['limit'=>self::MainLimit]);

        $this->tdk->short_name_zh = $comp->short_name_zh ?? '';
        $this->getTdk('liansai_zuqiu_detail',$this->tdk);
        //var_dump($luxiang);die;
        View::assign('data',$matchList);
        View::assign('luxiang',$luxiang);
        View::assign('jijin',$jijin);
        View::assign('article',$article);
        View::assign('data_info',$comp);
    }

    protected function getCompList($param)
    {
        $this->getTempPath('liansai_zuqiu');
        //赛程id
        $keyword = $param['keyword'] ?? '';
        $param['page'] = $param['page'] ?? 1;
        $param['limit'] = 24;

        $footballModel = new FootballCompetition();
        $data = $footballModel->getList($keyword,$param);
        $data['per_page'] = $param['limit'];
        $data['current_page'] = $param['page'];

        $this->getTdk('liansai_zuqiu',$this->tdk);
        View::assign('data',$data);
    }
}