<?php

namespace app\home\controller\liansai;

use app\commonModel\Article;
use app\commonModel\BasketballCompetition;
use app\commonModel\BasketballMatch;
use app\commonModel\MatchVedio;
use app\home\BaseController;
use app\home\Tdk;
use think\App;
use think\facade\View;

class Lanqiu extends BaseController
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
        $this->getTempPath('liansai_lanqiu_detail');

        //联赛数据
        $comp = BasketballCompetition::where('id',$compid)->findOrEmpty();

        if ($comp->isEmpty()) {
            abort(404, '参数错误');
        }

        $matchModel = new BasketballMatch();
        $doneData = $matchModel->getCompetitionListByDone($comp['id'],self::MainLimit);
        if(!$doneData){
            $basketballComp = getBasketballHotComp();
            $hotBasketballCompId = array_column($basketballComp,'id');
            $doneData = $matchModel->getTodayData($hotBasketballCompId,[10],self::MainLimit);
        }


        //直播数据
        $matchList = $matchModel->getMatchInfo([['status_id','IN',[1,2,3,4,5,7,8,9]],['match_time','>',time()-8000]],[$compid],self::MainLimit);
        if (empty($matchList)){
            $basketballComp = getBasketballHotComp();
            $hotBasketballCompId = array_column($basketballComp,'id');
            $matchList = $matchModel->getTodayData($hotBasketballCompId,[1,2,3,4,5,6,7,8,9],self::MainLimit);
        }

        $matchList = array_merge($matchList,$doneData);

        $videoModel = new MatchVedio();
        $matchId = BasketballMatch::where("competition_id",$compid)->where('match_time','<',time())->limit(200)->order('id','DESC')->column("id");
        //录像
        $luxiang = $videoModel->getByMatchId($matchId,1,self::MainLimit,2);

        //集锦
        $jijin = $videoModel->getByMatchId($matchId,1,self::MainLimit);

        //资讯
        $articleModel = new Article();
        $article = $articleModel->getListByCompId(1,['competition_id'=>$compid],['limit'=>self::MainLimit]);

        $this->tdk->short_name_zh =  empty($comp->short_name_zh) ? ($comp->name_zh ?? '') : $comp->short_name_zh;
        $this->getTdk('liansai_lanqiu_detail',$this->tdk);

        View::assign('data',$matchList);
        View::assign('luxiang',$luxiang);
        View::assign('jijin',$jijin);
        View::assign('article',$article);
        View::assign('data_info',$comp);
    }

    protected function getCompList($param)
    {
        $this->getTempPath('liansai_lanqiu');
        //赛程id
        $param['page'] = $param['page'] ?? 1;
        $param['limit'] = 24;
        //篮球数据
        $basketballModel = new BasketballCompetition();
        $data = $basketballModel->getList('',$param);
        $data['per_page'] = $param['limit'];
        $data['current_page'] = $param['page'];

        $this->getTdk('liansai_lanqiu',$this->tdk);
        View::assign('data',$data);
    }
}