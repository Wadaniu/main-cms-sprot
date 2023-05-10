<?php
/**
 * @copyright Copyright (c) 2021 勾股工作室
 * @license https://opensource.org/licenses/Apache-2.0
 * @link https://www.gougucms.com
 */
 
declare (strict_types = 1);

namespace app\admin\controller;

use app\admin\BaseController;
use app\admin\model\Pages as PagesModel;
use app\admin\validate\PagesValidate;
use think\exception\ValidateException;
use think\facade\Db;
use think\facade\View;

class Pages extends BaseController

{
	/**
     * 构造函数
     */
    public function __construct()
    {
        $this->model = new PagesModel();
		$this->uid = get_login_admin('id');

    }
    /**
    * 数据列表
    */
    public function datalist()
    {
        if (request()->isAjax()) {
			$param = get_params();
			$where = [];
			if (!empty($param['keywords'])) {
                $where[] = ['a.id|a.title|a.desc|a.content', 'like', '%' . $param['keywords'] . '%'];
            }
            $where[] = ['a.delete_time', '=', 0];
            $list = $this->model->getPagesList($where, $param);
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
        if (request()->isAjax()) {		
			$param = get_params();	
			
            // 检验完整性
            try {
                validate(PagesValidate::class)->check($param);
            } catch (ValidateException $e) {
                // 验证失败 输出错误信息
                return to_assign(1, $e->getError());
            }
			$param['admin_id'] = $this->uid;
            $this->model->addPages($param);
        }else{
			$templates = get_file_list(CMS_ROOT . '/app/home/view/pages/');
			View::assign('templates', $templates);
			return view();
		}
    }
	

    /**
    * 编辑
    */
    public function edit()
    {
		$param = get_params();
		
        if (request()->isAjax()) {			
            // 检验完整性
            try {
                validate(PagesValidate::class)->check($param);
            } catch (ValidateException $e) {
                // 验证失败 输出错误信息
                return to_assign(1, $e->getError());
            }
            $this->model->editPages($param);
        }else{
			$id = isset($param['id']) ? $param['id'] : 0;
			$detail = $this->model->getPagesById($id);
			if (!empty($detail)) {
				//轮播图
				if(!empty($detail['banner'])) {
					$detail['banner_array'] = explode(',',$detail['banner']);
				}
				//关键字
				$keyword_array = Db::name('PagesKeywords')
					->field('i.aid,i.keywords_id,k.title')
					->alias('i')
					->join('keywords k', 'k.id = i.keywords_id', 'LEFT')
					->order('i.create_time asc')
					->where(array('i.aid' => $id, 'k.status' => 1))
					->select()->toArray();

				$detail['keyword_ids'] = implode(",", array_column($keyword_array, 'keywords_id'));
				$detail['keyword_names'] = implode(',', array_column($keyword_array, 'title'));		
				$detail['keyword_array'] = $keyword_array;
				
				$templates = get_file_list(CMS_ROOT . '/app/home/view/pages/');
				View::assign('templates', $templates);
				View::assign('detail', $detail);
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
		$detail = $this->model->getPagesById($id);
		if (!empty($detail)) {
			//轮播图
			if(!empty($detail['banner'])) {
				$detail['banner_array'] = explode(',',$detail['banner']);
			}
			//关键字
			$keyword_array = Db::name('PagesKeywords')
				->field('i.aid,i.keywords_id,k.title')
				->alias('i')
				->join('keywords k', 'k.id = i.keywords_id', 'LEFT')
				->order('i.create_time asc')
				->where(array('i.aid' => $id, 'k.status' => 1))
				->select()->toArray();

			$detail['keyword_ids'] = implode(",", array_column($keyword_array, 'keywords_id'));
			$detail['keyword_names'] = implode(',', array_column($keyword_array, 'title'));		
			$detail['keyword_array'] = $keyword_array;
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
		$param = get_params();
		$id = isset($param['id']) ? $param['id'] : 0;
		$type = isset($param['type']) ? $param['type'] : 0;

        $this->model->delPagesById($id,$type);
   }
}
