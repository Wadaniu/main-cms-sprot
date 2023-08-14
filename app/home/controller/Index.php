<?php
/**
 * @copyright Copyright (c) 2021 勾股工作室
 * @license https://opensource.org/licenses/Apache-2.0
 * @link https://www.gougucms.com
 */

declare (strict_types = 1);

namespace app\home\controller;

use app\commonModel\BasketballMatch;
use app\commonModel\FootballMatch;
use app\home\BaseController;
use app\home\Tdk;
use think\App;
use think\facade\Cache;
use think\facade\Env;
use think\facade\View;

class Index extends BaseController
{
    const RouteTag  = 'index';

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->getTempPath(self::RouteTag);
    }
    public function index(){
        $basketballComp = getBasketballHotComp();
        $footballComp = getFootballHotComp();

        $hotFootballCompId = array_column($footballComp,'id');
        $hotBasketballCompId = array_column($basketballComp,'id');

        //获取热门联赛一周内数据
        //足球数据
        $footballModel = new FootballMatch();
        $footballData = $footballModel->getTodayData($hotFootballCompId);
        //篮球数据
        $basketballModel = new BasketballMatch();
        $basketballData = $basketballModel->getTodayData($hotBasketballCompId);
     $footballDone = [];
        foreach ($footballData as $k=>$v){
            if($v['status_id']=='8'){
                array_unshift($footballDone,$v);
                unset($footballData[$k]);
            }
        }
        $basketballDone = [];
        foreach ($basketballData as $k=>$v){
            if($v['status_id']=='10'){
                array_unshift($basketballDone,$v);
                unset($basketballData[$k]);
            }
        }
     $matchData = array_merge($footballData,$footballDone,$basketballData,$basketballDone);

        $res = [];
        foreach ($matchData as $item){
            $res[date('Y-m-d',$item['match_time'])][] = $item;
        }


        /*足球预测*/
        $where = [
            ["match_time",">=",time()],
            ["forecast","NOT NULL","NOT NULL"]
        ];
        $model = (new FootballMatch());
        $list = $model->getFootballMatchList($where,["order"=>"match_time asc","limit"=>5])->toArray();
        $footballTeam = new \app\commonModel\FootballTeam();
        $comp = new \app\commonModel\FootballCompetition();
        foreach ($list['data'] as $k=>$v){
            $list["data"][$k]['home'] = [];
            $list["data"][$k]['away'] = [];
            $home = $footballTeam->getShortNameZhLogo($v["home_team_id"]);
            $list["data"][$k]['home'] = $home;
            $away = $footballTeam->getShortNameZhLogo($v["away_team_id"]);
            $list["data"][$k]['away'] = $away;
            $list['data'][$k]['comp'] = $comp->getShortNameZh($v['competition_id']);
        }


        /*蓝球预测*/
        $model = (new BasketballMatch());
        $listLq = $model->getBasketballMatchList($where,["order"=>"match_time asc","limit"=>5])->toArray();
        $footballTeam = new \app\commonModel\BasketballTeam();
        $comp = new \app\commonModel\BasketballCompetition();
        foreach ($listLq['data'] as $k=>$v){
            $listLq["data"][$k]['home'] = [];
            $listLq["data"][$k]['away'] = [];
            $home = $footballTeam->getShortNameZhLogo($v["home_team_id"]);
            $listLq["data"][$k]['home'] = $home;
            $away = $footballTeam->getShortNameZhLogo($v["away_team_id"]);
            $listLq["data"][$k]['away'] = $away;
            $listLq['data'][$k]['comp'] = $comp->getShortNameZh($v['competition_id']);
        }

        //处理tdk
        $tdk = new Tdk();
        $this->getTdk(self::RouteTag,$tdk);

        View::assign('data',$res);
        View::assign('zuqiuforcast',$list);//var_dump($list);die;
        View::assign('lanqiuforcast',$listLq);
        return View::fetch($this->tempPath);
    }

}
