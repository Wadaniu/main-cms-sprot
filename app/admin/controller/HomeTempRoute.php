<?php
/**
 * @copyright Copyright (c) 2021 勾股工作室
 * @license https://opensource.org/licenses/Apache-2.0
 * @link https://www.gougucms.com
 */
 
declare (strict_types = 1);

namespace app\admin\controller;

use app\admin\BaseController;
use app\admin\validate\HomeTempRouteValidate;
use app\commonModel\HomeTempRoute as HomeTempRouteModel;
use think\exception\ValidateException;
use think\facade\View;

class HomeTempRoute extends BaseController

{
	/**
     * 构造函数
     */
    public function __construct()
    {
        $this->model = new HomeTempRouteModel();
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
			
            $list = $this->model->getHomeTempRouteList($where,$param);
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
                validate(HomeTempRouteValidate::class)->check($param);
            } catch (ValidateException $e) {
                // 验证失败 输出错误信息
                return to_assign(1, $e->getError());
            }

            //将模板压缩包解压
            uzip($param['temp_AP'],get_config('filesystem.disks.web_view_temp.root'));

            $this->model->addHomeTempRoute($param);
        }else{
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
                validate(HomeTempRouteValidate::class)->check($param);
            } catch (ValidateException $e) {
                // 验证失败 输出错误信息
                return to_assign(1, $e->getError());
            }

            if (isset($param['temp_AP']) && !empty($param['temp_AP'])){
                //将模板压缩包解压
                uzip($param['temp_AP'],get_config('filesystem.disks.web_view_temp.root'));
            }
            if (isset($param['wap_temp_AP']) && !empty($param['wap_temp_AP'])){
                //将模板压缩包解压
                uzip($param['temp_AP'],get_config('filesystem.disks.wep_view_temp.root'));
            }

            $this->model->editHomeTempRoute($param);
        }else{
			$id = isset($param['id']) ? $param['id'] : 0;
			$detail = $this->model->getHomeTempRouteById($id);
			if (!empty($detail)) {
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
		$detail = $this->model->getHomeTempRouteById($id);
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
	* type=0,逻辑删除，默认
	* type=1,物理删除
    */
    public function del()
    {
        $param = get_params();
		$id = isset($param['id']) ? $param['id'] : 0;
		$type = isset($param['type']) ? $param['type'] : 0;

        $this->model->delHomeTempRouteById($id,$type);
   }

   public function edithomecss()
   {
       $param = get_params();

       if (request()->isAjax()) {

           if (!isset($param['temp_AP']) || empty($param['temp_AP'])){
               throw new \think\exception\HttpException(404, '参数错误');
           }

           //将模板压缩包解压
           uzip('../public'.$param['temp_AP'],get_config('filesystem.disks.home_css.root'),true);
           return to_assign();
       }else{
           return view();
       }
   }
}
