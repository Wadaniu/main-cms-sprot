<?php

namespace app\commonModel;
use think\facade\Cache;
use think\facade\Db;
use think\model;

class FootballMatchInfo extends Model
{
    protected $pk = 'match_id';
    protected $connection = 'compDataDb';
    const SyncLimit = 30;
    const SyncMatchInfoKey = 'syncFootballMatchInfoList';

    public function autoSync(){

        sleep(10);      //防止和同步赛程数据并发导致赛程id队列错误
        //取比赛id
        $idList = Cache::get(self::SyncMatchInfoKey);
        $model = \think\facade\Db::connect('compDataDb')->name("sphere_query_update");
        if (!$idList){
            $syncInfo = $model->where(['id' => self::SyncMatchInfoKey])->find();
            if ($syncInfo){
                $idList = json_decode($syncInfo['parmars'],true);
            }
        }

        //获取前60个id(api限制)
        $matchIds = array_splice($idList,0,self::SyncLimit);
        //将id重新保存
        Cache::set(self::SyncMatchInfoKey,$idList);
        $model->where('id',self::SyncMatchInfoKey)->update(['parmars'=>json_encode($idList,true)]);

        $FootballMatchCount = new \app\commonModel\FootballMatchCount();
        foreach ($matchIds as $matchId){
            $analysis = $FootballMatchCount->getMatchAnalysis($matchId);
            $teamStats = $FootballMatchCount->getMatchTeamStats($matchId);
            //$videos = $FootballMatchCount->getMatchVideoCollection($matchId);

            $matchInfo = [
                'match_id'  =>  $matchId,
                'history'   =>  json_encode($analysis['history'] ?? [],true),
                'future'    =>  json_encode($analysis['future'] ?? [],true),
                'info'      =>  json_encode($analysis['info'] ?? [],true),
                'team_stats'=>  json_encode($teamStats ?? [],true),
                'updated_at'=>  date('Y-m-d H:i:s',time()),
            ];

//            foreach ($videos as &$video){
//                $video['match_id'] = $matchId;
//                $video['video_type'] = 0;
//            }

            try {
                $model = self::findOrEmpty($matchId);
                $model->save($matchInfo);
//                if (!empty($videos)){
//                    Db::connect('compDataDb')->name('match_vedio')->insertAll($videos);
//                }
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        }

        $updateId = $FootballMatchCount->getChangeData(3);
        //获取视频更新Id
        $videoMatchIds = array_column($updateId,'match_id');

        foreach ($videoMatchIds as $matchId){
            $videos = $FootballMatchCount->getMatchVideoCollection($matchId);
            foreach ($videos as $video){
                $video['match_id'] = $matchId;
                $video['video_type'] = 0;
                if(!empty($video['title'])){
                    $vedioModel = MatchVedio::where('title',$video['title'])->findOrEmpty();
                    $vedioModel->save($video);
                }
            }
        }

        return true;
    }

    public function addMatchByLast()
    {
        //获取最近一个月的比赛
        $startTime = strtotime('-30 day');

        $matchIds = Db::connect('compDataDb')->name('football_match')
            ->where('match_time','>=',$startTime)->column('id');

        $model = \think\facade\Db::connect('compDataDb')->name("sphere_query_update");
        $syncInfo = $model->where('id',self::SyncMatchInfoKey)->findOrEmpty();

        if (empty($syncInfo)){
            $idList = $matchIds;
            $insert = [
                'parmars' => json_encode($idList,true),
                'id'    =>  self::SyncMatchInfoKey
            ];
            $model->insert($insert);
        }else{
            $idList = array_unique(array_merge($matchIds,json_decode($syncInfo['parmars'],true)));
            $syncInfo['parmars'] = json_encode($idList,true);
            $model->where('id',self::SyncMatchInfoKey)->update($syncInfo);
        }

        Cache::set(self::SyncMatchInfoKey,$idList);
    }

    public function getByMatchId($id)
    {
        return self::findOrEmpty($id);
    }
}