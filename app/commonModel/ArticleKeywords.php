<?php

namespace app\commonModel;
use think\facade\Db;
use think\model\Pivot;

class ArticleKeywords extends Pivot
{

    public function getByAid($aid = 0): array
    {
        $data = [];
        if ($aid <= 0){
            return $data;
        }
        $keywordIds = self::where("aid",$aid)->where('status',1)->column('keywords_id');
        if ($keywordIds){
            $data = Db::name('keywords')->where('id','in',$keywordIds)->select()->toArray();
        }
        return $data;
    }

    public function getByKeywordId($id = 0): array
    {
        $data = [];
        if ($id <= 0){
            return $data;
        }
        $data = self::field('aid')->where("keywords_id",$id)->where('status',1)->select()->toArray();

        return $data;
    }
}