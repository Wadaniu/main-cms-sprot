<?php
/**
 * @copyright Copyright (c) 2021 勾股工作室
 * @license https://opensource.org/licenses/Apache-2.0
 * @link https://www.gougucms.com
 */
namespace app\commonModel;
use think\model;
use think\facade\Cache;

class ArticleCate extends Model
{
    /**
    * 获取分页列表
    * @param $where
    * @param $param
    */
    public function getArticleCateList($where, $param)
    {
		$rows = empty($param['limit']) ? get_config('app.page_size') : $param['limit'];
		$order = empty($param['order']) ? 'id desc' : $param['order'];
        try {
            $list = $this->where($where)->order($order)->paginate($rows, false, ['query' => $param]);
			return table_assign(0, '', $list);
        } catch(\Exception $e) {
            return ['code' => 1, 'data' => [], 'msg' => $e->getMessage()];
        }
    }

    /**
    * 添加数据
    * @param $param
    */
    public function addArticleCate($param)
    {
		$insertId = 0;
        try {
            $param['create_time'] = time();
            $insertId = $this->insertGetId($param);
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
    public function editArticleCate($param)
    {
		try {
			$param['update_time'] = time();
			$this->where('id', $param['id'])->update($param);
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
    public function getArticleCateById($id)
    {
        $info = $this->where('id', $id)->find();
		return $info;
    }

    /**
    * 删除信息
    * @param $id
    * @param $type
    */
    public function delArticleCateById($id,$type=0)
    {
//		if($type==0){
//			//逻辑删除
//			try {
//				$this->where('id', $id)->update(['delete_time'=>time()]);
//				add_log('delete', $id);
//			} catch(\Exception $e) {
//				return to_assign(1, '操作失败，原因：'.$e->getMessage());
//			}
//		}
//		else{
			//物理删除
			try {
				$this->where('id', $id)->delete();
				add_log('delete', $id);
			} catch(\Exception $e) {
				return to_assign(1, '操作失败，原因：'.$e->getMessage());
			}
		//}
		return to_assign();
    }

    public function getCateAllById($id): array
    {
        return self::field('id')->where('id',$id)->whereOr('pid',$id)->select()->toArray();
    }



    /**
     * 获取足球子分类
     * */
    public function getFootCate(){
        $key = "FootballCate";
        $data = Cache::store('common_redis')->get($key);
        if($data){
            return $data;
        }
        $data = self::where("pid",1)->column("id");
        $data[] = 1;
        Cache::store('common_redis')->set($key,$data,300);
        return $data;
    }

    /**
     * 获取篮球子分类
     * */
    public function getBasketCate(){
        $key = "BasketballCate";
        $data = Cache::store('common_redis')->get($key);
        if($data){
            return $data;
        }
        $data = self::where("pid",2)->column("id");
        $data[] = 2;
        Cache::store('common_redis')->set($key,$data,300);
        return $data;
    }
}

