<?php

namespace app\admin\model;
use think\model;
class FootballIntelligence extends Model
{
    protected $connection = 'compDataDb';

    //自动获取数据同步（无权限,暂不处理）
    public function autoSync($isCache=true){
        $url = "/api/v5/football/intelligence/list";
        $apiDataInfo = getApiDataInfo($url,$isCache);
        if(empty($apiDataInfo)){
            return;
        }
        $params = array();
        if($apiDataInfo["update_field"]=="id"){
            $params["id"] = $apiDataInfo["max_id"];
            $params["limit"] = 500;
        }else{
            $params["time"] = $apiDataInfo["max_time"];
            $params["limit"] = 100;
        }
        $getApiInfo = getApiInfo($url,$params);
        var_dump($getApiInfo);die;

    }
}