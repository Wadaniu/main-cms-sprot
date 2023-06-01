<?php

namespace app\commonModel;
use think\model;

class MatchVedio extends Model
{
    protected $connection = 'compDataDb';

    public function getByMatchId($id, $video_type = 0,$limit = 0,$type = 1): array
    {
        $query = self::where(['match_id'=>$id])->where(['video_type'=>$video_type,'type'=>$type]);
        if ($limit > 0){
            $query->limit($limit);
        }
        return $query->select()->toArray();
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
            ];
        }
        return [
            'match'=>[],
            'competition'=>[],
        ];
    }

    function basketball($info){
        $match = BasketballMatch::where("id",$info->match_id)->find();
        if($match){
            $competition = BasketballCompetition::where("id",$match->competition_id)->find();
            return [
                'match'=>$match->toArray(),
                'competition'=>$competition->toArray(),
            ];
        }
        return [
            'match'=>[],
            'competition'=>[],
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