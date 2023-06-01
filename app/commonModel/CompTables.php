<?php
/**
 * @copyright Copyright (c) 2021 勾股工作室
 * @license https://opensource.org/licenses/Apache-2.0
 * @link https://www.gougucms.com
 */
namespace app\commonModel;
use think\Model;

class CompTables  extends Model
{
    protected $connection = 'compDataDb';

    //积分榜
    public function getCompCountByCompId($id,$type = 0)
    {
        $url = "/api/v5/football/competition/table/detail";

        if ($id <= 0){
           return [];
        }

        if ($type > 0){
            $url = "/api/v5/basketball/competition/table/detail";
        }

        //获取赛事统计数据
        $stat = $this->getStatByCompId($id,$type);

        $params['id'] = $id;

        $getApiInfo = getApiInfo($url,$params);
        if(isset($getApiInfo["code"]) && $getApiInfo["code"]==0){

            if (isset($getApiInfo['results']['tables']) && !empty($getApiInfo['results']['tables'])){
                $insert = [
                    'comp_id'   =>  $id,
                    'type'      =>  $type,
                    'tables'    =>  json_encode($getApiInfo['results']['tables']??''),
                    'shooters'  =>  json_encode($stat['shooters']??''),
                    'assists'   =>  json_encode($stat['assists']??''),
                    'players_stats' =>  json_encode($stat['players_stats']??''),
                    'teams_stats'   =>  json_encode($stat['teams_stats']??'')
                ];

                $model = self::where(['comp_id'=> $id,'type'=>$type])->findOrEmpty();
                return $model->save($insert);
            }
        }
        return true;
    }

    //射手榜
    public function getStatByCompId($id,$type = 0)
    {
        $url = "/api/v5/football/competition/stats/detail";

        if ($id <= 0){
            return [];
        }

        if ($type > 0){
            $url = '/api/v5/basketball/competition/stats/detail';
        }

        $params['id'] = $id;

        $data = [];

        $getApiInfo = getApiInfo($url,$params);
        if($getApiInfo["code"]==0){

            $data = $getApiInfo['results'];

            if ($type == 0){
                $apiData = $getApiInfo['results']['shooters'];
                //助攻修改数据排序
                array_multisort(array_column($apiData,'assists'),SORT_DESC,$apiData);
                $data['assists'] = $apiData;
            }
        }
        return $data;
    }
}

