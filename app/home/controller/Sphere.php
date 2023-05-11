<?php
/**
 * @copyright Copyright (c) 2021 勾股工作室
 * @license https://opensource.org/licenses/Apache-2.0
 * @link https://www.gougucms.com
 */

declare (strict_types = 1);

namespace app\home\controller;

use app\commonModel\BasketballCompetition;
use app\commonModel\BasketballCountry;
use app\commonModel\BasketballMatch;
use app\commonModel\BasketballTeam;
use app\commonModel\FootballCompetition;
use app\commonModel\FootballCountry;
use app\commonModel\FootballMatch;
use app\commonModel\FootballTeam;
use app\home\BaseController;
use think\facade\View;

class Sphere extends BaseController
{



    public function team()
    {
        $param = get_params();
        $id = $param['id'];
        $type = $param['type'];
        $_GET['$type'] = $type;
        if($type == "basketball"){
            $team = new  BasketballTeam();
            $match = new  BasketballMatch();
            $competition = new  BasketballCompetition();
            $modelByInfo = $team->getBasketballTeamById($id);
            $competitionInfo = $competition->getBasketballCompetitionById($modelByInfo->competition_id,"name_zh,short_name_zh");
        }else{
            $team = new  FootballTeam();
            $match = new  FootballMatch();
            $competition = new  FootballCompetition();
            $modelByInfo = $team->getFootballTeamById($id);
            $competitionInfo = $competition->getFootballCompetitionById($modelByInfo->competition_id,"name_zh,short_name_zh");
        }
        if(empty($competitionInfo)){
            $competitionInfo = [
                "name_zh"=>"",
                "short_name_zh"=>"",
            ];
        }
        $matchList = $match->getTeamListInfo($modelByInfo->id);
        $y = date("Y");

        $seo=[
            "title"=> "{$modelByInfo['name_zh']}比赛直播_{$modelByInfo['name_zh']}赛程表_{$y}{$modelByInfo['name_zh']}篮球俱乐部最新赛程安排|视频直播免费观看",
            "keywords"=>"{$modelByInfo['name_zh']}赛程表,{$modelByInfo['name_zh']}赛程安排,{$modelByInfo['name_zh']}比赛直播",
            "description"=>"{$competitionInfo['short_name_zh']}直播{$modelByInfo['name_zh']}直播赛事频道,为广大球迷提供{$y}{$modelByInfo['name_zh']}篮球俱乐部最新比赛赛程安排、视频直播、录像回放等在线免费观看服务，{$competitionInfo['short_name_zh']}直播努力做最好的篮球赛事免费直播平台",
        ];

        View::assign('model',$modelByInfo);
        View::assign('competitionNameZh',$competitionInfo["name_zh"]);
        View::assign('matchList',$matchList);
        View::assign('seo',$seo);
        View::assign('typeText',"篮球");
        View::assign('type',$type);
        return view();
    }


    /**
     * 联赛
     */
    public function competition(){
        $param = get_params();
        $type   = $param['type'];
        $id     = $param['id'];
        $_GET['type'] = $type;
        if($type == "basketball"){
            $match          = new  BasketballMatch();
            $competition    = new  BasketballCompetition();
            $country        = new  BasketballCountry();
            $info = $competition->getBasketballCompetitionById($id);
            $matchList = $match->getCompetitionListInfo($info->id);
            $countryModel = $country->getBasketballCountryById($info->country_id,"name_zh");
            $typeText = "蓝球";
        }else {
            $match          = new  FootballMatch();
            $competition    = new  FootballCompetition();
            $country        = new  FootballCountry();
            $info = $competition->getFootballCompetitionById($id);
            $matchList = $match->getCompetitionListInfo($info->id);
            $countryModel = $country->getFootballCountryById($info->country_id,"name_zh");
            $typeText = "足球";
        }


        $countryNameText = "";
        if(!empty($countryModel)){
            $countryNameText = $countryModel->name_zh;
        }
        $y = date("Y");
        $seo=[
            "title"=> "{$info['short_name_zh']}直播_{$info['short_name_zh']}赛程安排_{$y}{$info['short_name_zh']}联赛比赛直播|视频|录像在线免费观看 -{$info['short_name_zh']}直播",
            "keywords"=>"{$info['short_name_zh']}在线直播,{$info['short_name_zh']}比赛赛程,{$info['short_name_zh']}直播在线观看,{$info['short_name_zh']}赛程安排,{$y}{$info['short_name_zh']}赛程",
            "description"=>"{$info['short_name_zh']}直播{$info['short_name_zh']}直播赛事频道,为广大球迷提供{$y}{$info['short_name_zh']}联赛最新比赛赛程安排、视频直播、录像回放等在线免费观看服务，{$info['short_name_zh']}直播努力做最好的篮球赛事免费直播平台。",
        ];
        View::assign('type',$type);
        View::assign('typeText',$typeText);
        View::assign('countryNameText',$countryNameText);
        View::assign('model',$info);
        View::assign('matchList',$matchList);
        View::assign('seo',$seo);

        return view();
    }


    public function basketball(){

        $param = get_params();
        $param["limit"] = 40;
        $param["page"] = 0;
        $where[] = ['logo', '<>',  ""];

        $competition = new BasketballCompetition();
        $model = $competition->getBasketballCompetitionList($where, $param);
        View::assign('model',$model);
        View::assign('items',$model->items());
        View::assign('type',"basketball");
        //View::assign('hotLive',$this->hotLive());
        return view("competitionlist");
    }
    public function football(){
        $param = get_params();
        $param["limit"] = 40;
        $param["page"] = 0;
        $where[] = ['logo', '<>',  ""];
        $competition = new FootballCompetition();
        $model = $competition->getFootballCompetitionList($where, $param);
        View::assign('model',$model);
        View::assign('items',$model->items());
        View::assign('type',"football");
       // View::assign('hotLive',$this->hotLive());
        return view("competitionlist");
    }


    /**
     * 比赛id
     */
    public function match(){
        $param = get_params();
        $type = $param["type"];
        $id = $param["id"];
        if($type == "basketball"){
            $matchModel         = new  BasketballMatch();
            $teamModel          = new  BasketballTeam();
            $competitionModel   = new  BasketballCompetition();
            $model = $matchModel->getBasketballMatchById($id);
            $homeTeam   = $teamModel->getBasketballTeamById($model->home_team_id);
            $awayTeam = $teamModel->getBasketballTeamById($model->away_team_id);
            $competitionName = $competitionModel->getShortNameZh($model->competition_id);
        }else{
            $matchModel         = new  FootballMatch();
            $teamModel          = new  FootballTeam();
            $competitionModel   = new  FootballCompetition();
            $model = $matchModel->getFootballMatchById($id);
            $homeTeam   = $teamModel->getFootballTeamById($model->home_team_id);
            $awayTeam = $teamModel->getFootballTeamById($model->away_team_id);
            $competitionName = $competitionModel->getShortNameZh($model->competition_id);
        }
        if(!empty($homeTeam)){
            if(empty($homeTeam->short_name_zh)){
                $homeTeam->short_name_zh = $homeTeam->name_zh;
            }
            if(empty($awayTeam->short_name_zh)){
                $awayTeam->short_name_zh = $awayTeam->name_zh;
            }
        }

        //$hotLive = $this->hotLive();

        View::assign('model',$model);
        View::assign('competitionName',$competitionName);
        View::assign('homeTeam',$homeTeam);
        View::assign('awayTeam',$awayTeam);
        View::assign('typeNameText',"篮球");
       // View::assign('hotLive',$hotLive);

        return view();
    }


    /**
     * 热点直播
     */
    protected function hotLive(){
        $basketballCompetition = new  BasketballCompetition();
        $basketballHotData = $basketballCompetition->getHotData();
        $footballCompetition = new  FootballCompetition();
        $footballHotData = $footballCompetition->getHotData();
        $basketballIds = array();
        $footballIds = array();
        foreach ($basketballHotData as $vo){
            $basketballIds[] = $vo["id"];
        }
        foreach ($footballHotData as $vo){
            $footballIds[] = $vo["id"];
        }
        $basketballMatch = new BasketballMatch();
        $footballMatch = new FootballMatch();
        $startTime =  strtotime(date('Y-m-d', strtotime('-2 days')));
        $endTime = strtotime(date("Y-m-d",strtotime("+1 days")))-1;
        $where[] = ['match_time','between',[$startTime,$endTime]];
        $basketball = $basketballMatch->getMatchInfo($where,$basketballIds,6,"match_time desc");
        $football   = $footballMatch->getMatchInfo($where,$footballIds,6,"match_time desc");
        $voList = array_merge($basketball,$football);
        $data = [];
        foreach ($voList as $key=> $vo){
            if($key<6){
                $data[] = $vo;
            }else{
                break;
            }

        }
        return $data;
    }


}
