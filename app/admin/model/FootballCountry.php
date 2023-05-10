<?php
/**
 * @copyright Copyright (c) 2021 勾股工作室
 * @license https://opensource.org/licenses/Apache-2.0
 * @link https://www.gougucms.com
 */
namespace app\admin\model;
use think\model;
class FootballCountry extends Model
{
    protected $connection = 'compDataDb';

    /**
    * 获取分页列表
    * @param $where
    * @param $param
    */
    public function getFootballCountryList($where, $param)
    {
		$rows = empty($param['limit']) ? get_config('app . page_size') : $param['limit'];
		$order = empty($param['order']) ? 'id desc' : $param['order'];
        $list = self::where($where)->field('id,category_id,name_zh,name_zht,name_en,logo,updated_at')
            ->order($order)
            ->paginate($rows, false, ['query' => $param])
            ->each(function ($item, $key) {
                $item->updated_at = date("Y-m-d H:i:s",$item->updated_at);
            });
		return $list;
    }

    /**
    * 添加数据
    * @param $param
    */
    public function addFootballCountry($param)
    {
		$insertId = 0;
        try {
			$param['create_time'] = time();
			$insertId = self::strict(false)->field(true)->insertGetId($param);
			add_log('add', $insertId, $param);
        } catch(\Exception $e) {
			return to_assign(1, '操作失败，原因：'.$e->getMessage());
        }
		return to_assign(0,'操作成功',['aid'=>$insertId]);
    }

    /**
    * 编辑信息
    * @param $param
    */
    public function editFootballCountry($param)
    {
        try {
            $param['update_time'] = time();
            self::where('id', $param['id'])->strict(false)->field(true)->update($param);
			add_log('edit', $param['id'], $param);
        } catch(\Exception $e) {
			return to_assign(1, '操作失败，原因：'.$e->getMessage());
        }
		return to_assign();
    }
	

    /**
    * 根据id获取信息
    * @param $id
    */
    public function getFootballCountryById($id)
    {
        $info = self::where('id', $id)->find();
		return $info;
    }

    /**
    * 删除信息
    * @param $id
    * @return array
    */
    public function delFootballCountryById($id,$type=0)
    {
		if($type==0){
			//逻辑删除
			try {
				$param['delete_time'] = time();
				self::where('id', $id)->update(['delete_time'=>time()]);
				add_log('delete', $id);
			} catch(\Exception $e) {
				return to_assign(1, '操作失败，原因：'.$e->getMessage());
			}
		}
		else{
			//物理删除
			try {
				self::where('id', $id)->delete();
				add_log('delete', $id);
			} catch(\Exception $e) {
				return to_assign(1, '操作失败，原因：'.$e->getMessage());
			}
		}
		return to_assign();
    }


    public function sync(){

        $info = self::where([])->select();
        $ids = [];
        foreach ($info as $row){
            $ids[] = $row->id;
        }
        $getApiInfo = getApiInfo("/api/v5/football/country/list");
        if($getApiInfo["code"]==0){
            $param = [];
            foreach ($getApiInfo["results"] as $vo){
                if(!in_array($vo["id"],$ids)){
                    $param[] = $vo;
                }
            }
            if(!empty($param)){
                try {
                    self::strict(false)->field(true)->insertAll($param);

                } catch(\Exception $e) {
                    return to_assign(1, '操作失败，原因：'.$e->getMessage());
                }
            }
            return to_assign(0,'已成功同步');
        }

    }
}

