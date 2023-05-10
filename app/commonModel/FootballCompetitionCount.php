<?php
/**
 * @copyright Copyright (c) 2021 勾股工作室
 * @license https://opensource.org/licenses/Apache-2.0
 * @link https://www.gougucms.com
 */
namespace app\commonModel;
use app\admin\model\FootballPlayer;
use think\facade\Cache;
use app\admin\model\FootballTeam;

class FootballCompetitionCount
{
    const SHOOT_LIMIT = 30;

    //积分榜
    public function getFootballCompCountByCompId($id)
    {
        $url = "/api/v5/football/competition/table/detail";

        if ($id <= 0){
           return [];
        }
        $redisKey = 'footballCompCount'.$id;
        $data = Cache::get($redisKey);
        if ($data){
            return json_decode($data,true);
        }

        $params['id'] = $id;

        $getApiInfo = getApiInfo($url,$params);
        if(isset($getApiInfo["code"]) && $getApiInfo["code"]==0){
            $data = [];
            foreach ($getApiInfo['results']['tables'] as $key => $value)
            {
                $team = [];
                $teamIdArr = array_column($value['rows'] ,'team_id');
                $teamArr = FootballTeam::field('id,short_name_zh,logo')->where('id','IN',$teamIdArr)->select()->toArray();
                $teamArr = array_column($teamArr,null,'id');

                foreach ($value['rows'] as $k => $teamCount){
                    $teamCount['team_name'] = $teamArr[$teamCount['team_id']]['short_name_zh'];
                    $teamCount['team_logo'] = $teamArr[$teamCount['team_id']]['logo'];
                    $team[$k] = $teamCount;
                }

                $data[$key]['id'] = $value['id'];
                $data[$key]['conference'] = $value['conference'];
                $data[$key]['group'] = $value['group'];
                $data[$key]['stage_id'] = $value['stage_id'];
                $data[$key]['rows'] = $team;
            }

            Cache::set($redisKey, json_encode($data),3600);
        }
        return $data;
    }

    //射手榜
    public function getShootCountByCompId($id,$type)
    {
        $url = "/api/v5/football/competition/stats/detail";

        if ($id <= 0){
            return [];
        }

        if ($type == 0){
            //射手榜缓存
            $redisKey = 'footballCompShootCount'.$id;
        }else{
            //助攻榜缓存
            $redisKey = 'footballSACount'.$id;
        }

        $data = Cache::get($redisKey);
        if ($data){
            return json_decode($data,true);
        }

        $params['id'] = $id;

        $getApiInfo = getApiInfo($url,$params);
        if($getApiInfo["code"]==0){
            $data = [];

            $apiData = $getApiInfo['results']['shooters'];

            if ($type > 0){
                //助攻修改数据排序
                array_multisort(array_column($apiData,'assists'),SORT_DESC,$apiData);
            }

            $teamIdArr = [];
            $playerIdArr = [];

            foreach ($apiData as $key => $value)
            {
                if ($key >= self::SHOOT_LIMIT){
                    break;
                }
                //获取球队信息
                $teamIdArr[] = $value['team_id'];
                $playerIdArr[] = $value['player_id'];
            }

            $teamInfoArr = FootballTeam::field('id,short_name_zh,logo')->where('id','IN',$teamIdArr)->select()->toArray();
            $teamInfoArr = array_column($teamInfoArr,null,'id');
            $playerInfoArr = FootballPlayer::field('id,name_zh,logo')->where('id','IN',$playerIdArr)->select()->toArray();
            $playerInfoArr = array_column($playerInfoArr,null,'id');

            foreach ($apiData as $key => $value) {
                if ($key >= self::SHOOT_LIMIT){
                    break;
                }
                $value['team_name'] = $teamInfoArr[$value['team_id']]['short_name_zh'] ?? '';
                $value['team_logo'] = $teamInfoArr[$value['team_id']]['logo'] ?? '';
                $value['player_name'] = $playerInfoArr[$value['player_id']]['name_zh'] ?? '';
                $value['player_logo'] = $playerInfoArr[$value['player_id']]['logo'] ?? '';

                $data[] = $value;
            }
            Cache::set($redisKey, json_encode($data),3600);
        }
        return $data;
    }
}

