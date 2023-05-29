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
            //->toArray()
        ;

        $data = $list->toArray();
        $data['render'] = $list->render();
        return $data;
    }
}