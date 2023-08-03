<?php
/**
 * @copyright Copyright (c) 2021 勾股工作室
 * @license https://opensource.org/licenses/Apache-2.0
 * @link https://www.gougucms.com
 */
 
declare (strict_types = 1);

namespace app\admin\controller;

use app\admin\BaseController;
use app\admin\validate\ArticleValidate;
use app\commonModel\LabelModel;
use think\exception\ValidateException;
use think\facade\Db;
use think\facade\View;

class Label extends BaseController

{
    /**
    * 数据列表
    */
    public function datalist()
    {
        if (request()->isAjax()) {
			$param = get_params();
			$where = [];
			if (!empty($param['keywords'])) {
                $where[] = ['name', 'like', '%' . $param['keywords'] . '%'];
            }
            $ArticleModel = new LabelModel();
            $list = $ArticleModel->getLabelList($where, $param);
            return table_assign(0, '', $list);
        }
        else{
            return view();
        }
    }

    /**
    * 添加
    */
    public function add()
    {
        ini_set('max_execution_time', '0');
        if (request()->isAjax()) {		
			$param = get_params();
            $LabelModel = new LabelModel();
            $LabelModel->status=$param["status"];
            $LabelModel->name=$param["name"];
            $LabelModel->createdAt=time();
            $LabelModel->save();
            $LabelModel->postLabel();
            return to_assign();
        }else{
			View::assign('editor', get_system_config('other','editor'));
			return view();
		}
    }
	

    /**
    * 编辑
    */
    public function edit()
    {
        ini_set('max_execution_time', '0');
		$param = get_params();
        $LabelModel = new LabelModel();
        $detail = $LabelModel->getLabelById($param["id"]);
        if (request()->isAjax()) {
            if($param['isNewDes']==1 && $detail->isNewDes==0){
                $detail->postLabel();
            }
            $detail->status=$param["status"];
            $detail->name=$param["name"];
            $detail->isNewDes=$param["isNewDes"];
            $detail->save();
            return to_assign(0,'操作成功');
        }else{
			if (!empty($detail)) {
				View::assign('detail', $detail);
                View::assign('editor', get_system_config('other','editor'));
				return view();
			}
			else{
				throw new \think\exception\HttpException(404, '找不到页面');
			}			
		}
    }


    /**
    * 查看信息
    */
    public function read()
    {
        $param = get_params();
		$id = isset($param['id']) ? $param['id'] : 0;
        $LabelModel = new LabelModel();
		$detail = $LabelModel->getLabelById($id);
		if (!empty($detail)) {
			View::assign('detail', $detail);
			return view();
		}
		else{
			throw new \think\exception\HttpException(404, '找不到页面');
		}
    }

    /**
    * 删除
    */
    public function del()
    {
		$param = get_params();
		$id = isset($param['id']) ? $param['id'] : 0;
        $LabelModel = new LabelModel();
        $LabelModel->where("id",$id)->delete();
        return to_assign(0,'操作成功');
   }
}
