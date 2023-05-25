<?php

namespace app\commonModel;

class BasketballMatchCount
{
    const ANALYSIS_LIMIT = 5;

    //赛事统计
    public function getMatchAnalysis($id)
    {
        $url = "/api/v5/basketball/match/analysis";

        if ($id <= 0){
            return [];
        }

        $params['id'] = $id;
        $data = [];
        $getApiInfo = getApiInfo($url,$params);
        if($getApiInfo["code"]==0){

            $existTeam = [];
            $existComp = [];

            //历史交锋
            $data['history']['vs'] = $this->formatMatchCompAndTeam($getApiInfo['results']['history']['vs'],$existTeam,$existComp);
            //主场近期战绩
            $data['history']['home'] = $this->formatMatchCompAndTeam($getApiInfo['results']['history']['home'],$existTeam,$existComp);
            //客场近期战绩
            $data['history']['away'] = $this->formatMatchCompAndTeam($getApiInfo['results']['history']['away'],$existTeam,$existComp);

            //未来赛程
            $data['future']['vs'] = $this->formatMatchCompAndTeam($getApiInfo['results']['future']['vs'],$existTeam,$existComp);
            //主队近期赛程
            $data['future']['home'] = $this->formatMatchCompAndTeam($getApiInfo['results']['future']['home'],$existTeam,$existComp);
            //客队近期赛程
            $data['future']['away'] = $this->formatMatchCompAndTeam($getApiInfo['results']['future']['away'],$existTeam,$existComp);

            //赛事信息
            $data['info'] = $this->formatMatchCompAndTeam([$getApiInfo['results']['info']],$existTeam,$existComp)[0];
        }
        return $data;
    }

    /**
     * 将比赛id及team_id转化为名称
     * @param $arr
     * @param $existTeam
     * @param $existComp
     * @return array
     */
    private function formatMatchCompAndTeam($arr,&$existTeam,&$existComp): array
    {
        $data = [];
        foreach ($arr as $key => $value)
        {
            if ($key >= self::ANALYSIS_LIMIT){
                break;
            }

            if (!array_key_exists($value['competition_id'],$existComp)){
                //获取赛事信息
                $compName = (new BasketballCompetition)->getShortNameZh($value['competition_id'])['short_name_zh'];
                $existComp[$value['competition_id']] = $compName;
            }else{
                $compName = $existComp[$value['competition_id']];
            }

            if (!array_key_exists($value['home_team_id'],$existTeam)){
                //获取主场球队信息
                $homeTeamInfo = (new BasketballTeam)->getShortNameZhLogo($value['home_team_id']);
                $existTeam[$value['home_team_id']] = $homeTeamInfo;
            }else{
                $homeTeamInfo = $existTeam[$value['home_team_id']];
            }

            if (!array_key_exists($value['away_team_id'],$existTeam)){
                //获取客场球队信息
                $awayTeamInfo = (new BasketballTeam)->getShortNameZhLogo($value['away_team_id']);
                $existTeam[$value['away_team_id']] = $awayTeamInfo;
            }else{
                $awayTeamInfo = $existTeam[$value['away_team_id']];
            }

            $value['competition_text'] = $compName;
            $value['home_team_text'] = $homeTeamInfo['short_name_zh']??'';
            $value['home_team_logo'] = $homeTeamInfo['logo']??'';
            $value['away_team_text'] = $awayTeamInfo['short_name_zh']??'';
            $value['away_team_logo'] = $awayTeamInfo['logo']??'';
            $value['status_text'] = FootballMatch::$STATUSID[$value['status_id']];
            $data[] = $value;
        }

        return $data;
    }

    public function getMatchPlayers($id)
    {
        $url = "/api/v5/basketball/match/live/history";

        if ($id <= 0){
            return [];
        }

        $params['id'] = $id;
        $data = [];
        $getApiInfo = getApiInfo($url,$params);
        if($getApiInfo["code"]==0){
            $data = $getApiInfo['results']['players'];
        }
        return $data;
    }

    public function getMatchVideoCollection($id)
    {
        $url = "/api/v5/basketball/match/stream/video_collection";

        if ($id <= 0){
            return [];
        }

        $params['id'] = $id;

        $data = getApiInfo($url,$params);
        if ($data['code'] != 0){
            return [];
        }
        return $data['results'];
    }

    /**
     * $typeId为更新数据id
     * @param $typeId       3	集锦录像
                            5	对阵图
                            6	积分榜
                            7	赛季球队球员统计
                            8	fiba men排名
                            9	fiba women排名
     * @return mixed
     */
    public function getChangeData($typeId = 0){
        $url = "/api/v5/football/data/more/update";

        $data = getApiInfo($url);
        if ($data['code'] != 0){
            return [];
        }
        if ($typeId > 0){
            return $data['results'][$typeId] ?? [];
        }

        return $data['results'];
    }
}