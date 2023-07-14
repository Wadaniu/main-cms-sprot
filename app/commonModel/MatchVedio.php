<?php

namespace app\commonModel;
use think\model;

class MatchVedio extends Model
{
    protected $connection = 'compDataDb';

    public function getByMatchId($id, $video_type = 0,$limit = 0,$type = 1)
    {
        $query = self::where(['match_id'=>$id])->where(['video_type'=>$video_type,'type'=>$type]);

        if ($query->count() <= 0){
            $query = self::where(['video_type'=>$video_type,'type'=>$type]);
        }

        if ($limit > 0){
            $query->limit($limit);
        }

        $data = $query->order('id','desc')->select()->toArray();

        $res = [];
        foreach ($data as $item){
            if ($video_type == 0){
                $compId = FootballMatch::where('id',$item['match_id'])->value('competition_id');
                $compInfo = (new FootballCompetition())->getShortNameZh($compId);
                $item['sphere_type'] = 'zuqiu';
            }else{
                $compId = BasketballMatch::where('id',$item['match_id'])->value('competition_id');
                $compInfo = (new BasketballCompetition())->getShortNameZh($compId);
                $item['sphere_type'] = 'lanqiu';
            }
            if(!empty($compInfo)){
                $item['short_name_py'] = $compInfo['short_name_py'];
                $item['title'] = replaceTitleWeb($item['title']);
                $res[] = $item;
            }

        }

        return $res;
    }


    public function getList($where, $param){
        $rows = empty($param['limit']) ? get_config('app . page_size') : $param['limit'];
        $page = $param['page'];
        $order = empty($param['order']) ? 'id desc' : $param['order'];
        $query = self::where($where);
        $count = $query->count();
        $list = $query->limit($page*$rows-$rows,$rows)->order($order)->select()->toArray();
        $res = [
            'total' => $count,
            'data'  => $list,
            'per_page'=>$rows,
            'current_page'=>$page
        ];
        return $res;
    }


    //获取集锦或者录像的赛事和赛程信息
    public function getCompetitionInfo($info){
        //$info = self::where('id', $id)->find();
        switch ($info['video_type']){
            case 0:
                return $this->football($info);
                break;
            case 1:
                return $this->basketball($info);
                break;
        }
    }


    function football($info){
        $match = (new FootballMatch())->getMatchInfo("id=".$info['match_id'],[],1);
        if($match){
            $competition = (new FootballCompetition())->getShortNameZh($match[0]['competition_id']);
            return [
                'match'=>$match[0],
                'competition'=>$competition,
                'home_team'=>(new FootballTeam())->getShortNameZhLogo($match[0]['home_team_id']),
                'away_team'=>(new FootballTeam())->getShortNameZhLogo($match[0]['away_team_id']),
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
        $match = (new BasketballMatch())->getMatchInfo("id=".$info['match_id'],[],1);;
        if($match){
            $competition = (new BasketballCompetition())->getShortNameZh($match[0]['competition_id']);
            return [
                'match'=>$match[0],
                'competition'=>$competition,
                'home_team'=>(new BasketballTeam())->getShortNameZhLogo($match[0]['home_team_id']),
                'away_team'=>(new BasketballTeam())->getShortNameZhLogo($match[0]['away_team_id']),
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