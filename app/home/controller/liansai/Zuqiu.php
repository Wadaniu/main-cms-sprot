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
    const MainLimit = 8;
    public function __construct(App $app)
    {
        parent::__construct($app);
    }
    public function index(){
        $param = $this->parmas;
        $compid = $param['compid'] ?? 0;

        if(!is_numeric($compid)){
            abort(404, '参数错误');
        }

        $this->tdk = new Tdk();

        if (empty($compid)){
            $this->getCompList($param);
        }else{
            $this->getCompInfo($compid);
        }
        return View::fetch($this->tempPath);
    }

    protected function getCompInfo($compid)
    {
        $this->getTempPath('liansai_zuqiu_detail');

        //联赛数据
        $comp = FootballCompetition::where('id',$compid)->findOrEmpty();
        if ($comp->isEmpty()) {
            abort(404, '参数错误');
        }

        $matchModel = new FootballMatch();
        $doneData = $matchModel->getCompetitionListByDone($comp['id'],self::MainLimit);
        if(!$doneData){
            $footballComp = getFootballHotComp();
            $hotFootballCompId = array_column($footballComp,'id');
            $doneData = $matchModel->getTodayData($hotFootballCompId,[8],self::MainLimit);
        }

        //直播数据

        $matchList = $matchModel->getMatchInfo([['status_id','IN',[1,2,3,4,5,7]],['match_time','>',time()-8000]],[$compid],self::MainLimit);
        if (empty($matchList)){
            $footballComp = getFootballHotComp();
            $hotFootballCompId = array_column($footballComp,'id');
            $matchList = $matchModel->getTodayData($hotFootballCompId,[1,2,3,4,5,7],self::MainLimit);
        }
        $matchList = array_merge($matchList,$doneData);
        $videoModel = new MatchVedio();
        $matchId = FootballMatch::where("competition_id",$compid)->where('match_time','<',time())->limit(200)->order('id','DESC')->column("id");

        //录像
        $luxiang = $videoModel->getByMatchId($matchId,0,self::MainLimit,2);
        //集锦
        $jijin = $videoModel->getByMatchId($matchId,0,self::MainLimit);

        //资讯
        $articleModel = new Article();
        $article = $articleModel->getListByCompId(0,['competition_id'=>$compid],['limit'=>self::MainLimit]);

        $this->tdk->short_name_zh =  empty($comp->short_name_zh) ? ($comp->name_zh ?? '') : $comp->short_name_zh;
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