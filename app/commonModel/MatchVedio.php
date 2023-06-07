<?php

namespace app\commonModel;
use think\model;

class MatchVedio extends Model
{
    protected $connection = 'compDataDb';

    public function getByMatchId($id, $video_type = 0,$limit = 0,$type = 1)
    {
        $query = self::where(['match_id'=>$id])->where(['video_type'=>$video_type,'type'=>$type]);
        if ($limit > 0){
            $query->limit($limit);
        }
        $data = $query->select()->toArray();

        $res = [];
        foreach ($data as $item){
            if ($video_type == 0){
                $compId = FootballMatch::where('id',$item['match_id'])->value('competition_id');
                $compInfo = (new FootballCompetition())->getShortNameZh($compId);
                $item['sphere_type'] = 'zuqiu';
                $item['short_name_py'] = $compInfo['short_name_py'];
            }else{
                $compId = BasketballMatch::where('id',$item['match_id'])->value('competition_id');
                $compInfo = (new BasketballCompetition())->getShortNameZh($compId);
                $item['sphere_type'] = 'lanqiu';
                $item['short_name_py'] = $compInfo['short_name_py'];
            }

            $res[] = $item;
        }

        return $res;
    }


    public function getList($where, $param){
        $rows = empty($param['limit']) ? get_config('app . page_size') : $param['limit'];
        $order = empty($param['order']) ? 'id desc' : $param['order'];
        $list = self::where($where)
            ->order($order)
            ->paginate($rows, false, ['query' => $param])
        ;
        return $list;
    }


    //获取集锦或者录像的赛事和赛程信息
    public function getCompetitionInfo($id){
        $info = self::where('id', $id)->find();
        switch ($info->video_type){
            case 0:
                return $this->football($info);
            case 1:
                return $this->basketball($info);
        }
    }


    function football($info){
        $match = FootballMatch::where("id",$info->match_id)->find();
        if($match){
            $competition = FootballCompetition::where("id",$match->competition_id)->find();
            return [
                'match'=>$match->toArray(),
                'competition'=>$competition->toArray(),
                'home_team'=>FootballTeam::where("id",$match->home_team_id)->field("id,name_zh as name")->find()->toArray(),
                'away_team'=>FootballTeam::where("id",$match->away_team_id)->field("id,name_zh as name")->find()->toArray(),
            ];
        }
        return [
            'match'=>[],
            'competition'=>[],
            'home_team'=>[],
            'away_team'=>[],
        ];
    }

    function basketball($info){
        $match = BasketballMatch::where("id",$info->match_id)->find();
        if($match){
            $competition = BasketballCompetition::where("id",$match->competition_id)->find();
            return [
                'match'=>$match->toArray(),
                'competition'=>$competition->toArray(),
                'home_team'=>BasketballTeam::where("id",$match->home_team_id)->field("id,name_zh as name")->find()->toArray(),
                'away_team'=>BasketballTeam::where("id",$match->away_team_id)->field("id,name_zh as name")->find()->toArray(),
            ];
        }
        return [
            'match'=>[],
            'competition'=>[],
            'home_team'=>[],
            'away_team'=>[],
        ];
    }



    function getListWithMatch($type,$video_type,$limit,$competition_id=0){
        $list = self::alias("a")->field('a.*')->where("type",$type);
        if($video_type==0){
            $list = $list->join("FootballMatch b","a.match_id=b.id");
        }else{
            $list = $list->join("BasketballMatch b","a.match_id=b.id");
        }
        if($competition_id){
            $list = $list->where("c.competition_id",$competition_id);
        }
        $list = $list->order("a.id desc");
        if($limit){
            $list = $list->limit($limit);
        }
        return $list;

    }
}