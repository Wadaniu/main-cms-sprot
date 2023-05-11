<?php

namespace app\commonModel;
use think\model;

class MatchVedio extends Model
{
    protected $connection = 'compDataDb';

    public function getByMatchId($id, int $type): array
    {
        return self::where('match_id',$id)->where('video_type',$type)->select()->toArray();
    }
}