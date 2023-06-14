<?php
/**
 * @copyright Copyright (c) 2021 勾股工作室
 * @license https://opensource.org/licenses/Apache-2.0
 * @link https://www.gougucms.com
 */
namespace app\commonModel;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\facade\Cache;
use think\model;

class FootballVenue extends Model
{
    protected $connection = 'compDataDb';

    public static $CACHE_HOME =  "FootballVenue";



    /**
     *获取教练信息
     */
    public function getBasketballVenueById($id){
        $key = self::$CACHE_HOME;
        $key .= $id;
        $data = Cache::store('common_redis')->get($key);
        if(!empty($data)){
           return $data;
        }

        $field="id,name_zh,capacity,name_en,country_id";
        $data = self::where(['id'=>$id])
            ->field($field)
            ->find();
        if($data){
            $data = $data->toArray();
            Cache::store('common_redis')->set($key,$data,120);
        }else{
            return [];
        }
        return $data;
    }


}

