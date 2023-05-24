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