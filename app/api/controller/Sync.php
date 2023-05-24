<?php
/**
 * @copyright Copyright (c) 2021 勾股工作室
 * @license https://opensource.org/licenses/Apache-2.0
 * @link https://www.gougucms.com
 */
 
declare (strict_types = 1);

namespace app\api\controller;

use app\api\BaseController;
use app\commonModel\BasketballMatch;
use app\commonModel\FootballMatch;


class Sync extends BaseController
{
    /**
     * 官方建议 1分一次
     * 篮球比赛
     */
    public function basketballMatch(){
        $match = new BasketballMatch();
        $match->autoSync();
    }

    /**
     * 官方建议 10分一次
     * 篮球直播
     */
    public function basketballFree(){
        $match = new BasketballMatch();
        $match->autoSyncUrlsFree();
    }

    /**
     * 官方建议 1分一次
     * 篮球团队
     */
    public function basketballTeam(){
        $team = new \app\commonModel\BasketballTeam();
        $team->autoSync();
    }
    /**
     * 联赛篮球
     */
    public function basketballCompetition(){
        $competition = new \app\commonModel\BasketballCompetition();
        $competition->autoSync();
    }


    /**
     * 官方建议 1分一次
     * 足球比赛
     */
    public function footballIncrementMatch(){
        $match = new FootballMatch();
        $match->autoSync();
    }

    /**
     * 官方建议 1分一次
     * 足球比赛
     */
    public function footballMatch(){
        $match = new FootballMatch();
        $match->autoSync(false);
    }

    /**
     * 官方建议 10分一次
     * 足球直播
     */
    public function footballFree(){
        $match = new FootballMatch();
        $match->autoSyncUrlsFree();
    }

    /**
     * 官方建议 1分一次
     * 足球团队
     */
    public function footballTeam(){
        $team = new \app\commonModel\FootballTeam();
        $team->autoSync();
    }
    /**
     * 足球联赛
     */
    public function footballCompetition(){
        $competition = new \app\commonModel\FootballCompetition();
        $competition->autoSync();
    }

    /**
     * 足球球员列表
     * @return void
     */
    public function footballPlayer(){
        $footballPlayer = new \app\commonModel\FootballPlayer();
        $footballPlayer->autoSync();
    }

    /**
     * 足球球员列表
     * @return void
     */
    public function footballMatchInfo(){
        $FootballMatchInfo = new \app\commonModel\FootballMatchInfo();
        $FootballMatchInfo->autoSync();
    }

    /**
     * 获取最近一个月所有比赛，用于同步赛程详情，同步一次即可
     * @return void
     */
    public function addFootballMatchInfoByLast(){
        $FootballMatchInfo = new \app\commonModel\FootballMatchInfo();
        $FootballMatchInfo->addMatchByLast();
    }

    /**
     * 同步篮球比赛详情
     * @return void
     */
    public function basketballMatchInfo(){
        $BasketballMatchInfo = new \app\commonModel\BasketballMatchInfo();
        $BasketballMatchInfo->autoSync();
    }

    /**
     * 获取最近一个月所有比赛，用于同步赛程详情，同步一次即可
     * @return void
     */
    public function addBasketballMatchInfoByLast(){
        $BasketballMatchInfo = new \app\commonModel\BasketballMatchInfo();
        $BasketballMatchInfo->addMatchByLast();
    }
}
