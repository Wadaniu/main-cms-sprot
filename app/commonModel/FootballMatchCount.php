<?php

namespace app\commonModel;
use app\admin\model\FootballCompetition;
use app\admin\model\FootballMatch;
use app\admin\model\FootballTeam;
use think\facade\Cache;

class FootballMatchCount
{
    const ANALYSIS_LIMIT = 5;

    //赛事统计
    public function getMatchAnalysis($id)
    {
        $url = "/api/v5/football/match/analysis";

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
                $compName = FootballCompetition::where('id',$value['competition_id'])->value('short_name_zh');
                $existComp[$value['competition_id']] = $compName;
            }else{
                $compName = $existComp[$value['competition_id']];
            }

            if (!array_key_exists($value['home_team_id'],$existTeam)){
                //获取主场球队信息
                $homeTeamInfo = FootballTeam::field('short_name_zh,logo')->find($value['home_team_id']);
                $existTeam[$value['home_team_id']] = $homeTeamInfo;
            }else{
                $homeTeamInfo = $existTeam[$value['home_team_id']];
            }

            if (!array_key_exists($value['away_team_id'],$existTeam)){
                //获取客场球队信息
                $awayTeamInfo = FootballTeam::field('short_name_zh,logo')->find($value['away_team_id']);
                $existTeam[$value['away_team_id']] = $awayTeamInfo;
            }else{
                $awayTeamInfo = $existTeam[$value['away_team_id']];
            }

            $value['competition_text'] = $compName;
            $value['home_team_text'] = $homeTeamInfo->short_name_zh??'';
            $value['home_team_logo'] = $homeTeamInfo->logo??'';
            $value['away_team_text'] = $awayTeamInfo->short_name_zh??'';
            $value['away_team_logo'] = $awayTeamInfo->logo??'';
            $value['status_text'] = FootballMatch::$STATUSID[$value['status_id']];
            $data[] = $value;
        }

        return $data;
    }

    public function getMatchTeamStats($id)
    {
        $url = "/api/v5/football/match/team_stats/detail";

        if ($id <= 0){
            return [];
        }

        $params['id'] = $id;
        $data = [];
        $getApiInfo = getApiInfo($url,$params);
        if($getApiInfo["code"]==0){
            foreach ($getApiInfo['results'] as $value){
                $teamInfo = FootballTeam::field('short_name_zh,logo')->find($value['team_id']);
                $value['team_name_text'] = $teamInfo->short_name_zh;
                $value['team_name_logo'] = $teamInfo->logo;
                $data[] = $value;
            }
        }
        return $data;
    }

    public function getMatchVideoCollection($id)
    {
        $url = "/api/v5/football/match/stream/video_collection";

        if ($id <= 0){
            return [];
        }

        $params['id'] = $id;

        $data = getApiInfo($url,$params);

        return $data['results'];
    }

    /**
     * $typeId为更新数据id
     * @param $typeId       2	单场阵容
                            3	集锦录像
                            5	对阵图
                            6	积分榜
                            7	赛季球队球员统计
                            8	fifa men排名
                            9	fifa women排名
                            10	俱乐部排名
     * @return mixed
     */
    public function getChangeData($typeId = 0){
        $url = "/api/v5/football/data/more/update";

        $data = getApiInfo($url);
        if ($typeId > 0){
            return $data['results'][$typeId] ?? [];
        }

        return $data['results'];
    }
}