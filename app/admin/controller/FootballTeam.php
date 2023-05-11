<?php
/**
 * @copyright Copyright (c) 2021 勾股工作室
 * @license https://opensource.org/licenses/Apache-2.0
 * @link https://www.gougucms.com
 */
 
declare (strict_types = 1);

namespace app\admin\controller;

use app\admin\BaseController;
use app\admin\validate\FootballTeamValidate;
use app\commonModel\FootballTeam as FootballTeamModel;
use think\exception\ValidateException;
use think\facade\View;

class FootballTeam extends BaseController

{
	/**
     * 构造函数
     */
    public function __construct()
    {
        $this->model = new FootballTeamModel();
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
                $where[] = ['name_zh|name_zht|name_en', 'like', '%' . $param['keywords'] . '%'];
            }
            $list = $this->model->getFootballTeamList($where,$param);
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
                validate(FootballTeamValidate::class)->check($param);
            } catch (ValidateException $e) {
                // 验证失败 输出错误信息
                return to_assign(1, $e->getError());
            }
			if(isset($param["updated_at"])){
					$param["updated_at"]= $param["updated_at"]?strtotime($param["updated_at"]):0;
				}
				
            $this->model->addFootballTeam($param);
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
                validate(FootballTeamValidate::class)->check($param);
            } catch (ValidateException $e) {
                // 验证失败 输出错误信息
                return to_assign(1, $e->getError());
            }
			if(isset($param["updated_at"])){
					$param["updated_at"]= $param["updated_at"]?strtotime($param["updated_at"]):0;
				}
				
            $this->model->editFootballTeam($param);
        }else{
			$id = isset($param['id']) ? $param['id'] : 0;
			$detail = $this->model->getFootballTeamById($id);
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
		$detail = $this->model->getFootballTeamById($id);
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

        $this->model->delFootballTeamById($id,$type);
   }
    /**
     * 同步数据
     */
    public function sync(){
        $this->model->sync();
    }
}
