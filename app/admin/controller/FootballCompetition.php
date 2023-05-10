<?php
/**
 * @copyright Copyright (c) 2021 勾股工作室
 * @license https://opensource.org/licenses/Apache-2.0
 * @link https://www.gougucms.com
 */
 
declare (strict_types = 1);

namespace app\admin\controller;

use app\admin\BaseController;
use app\admin\model\FootballCompetition as FootballCompetitionModel;
use app\admin\validate\FootballCompetitionValidate;
use think\exception\ValidateException;
use think\facade\Db;
use think\facade\View;

class FootballCompetition extends BaseController

{
	/**
     * 构造函数
     */
    public function __construct()
    {
        $this->model = new FootballCompetitionModel();
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
                $where[] = ['name_zh|short_name_zh', 'like',  $param['keywords'] . '%'];
            }
            $list = $this->model->getFootballCompetitionList($where,$param);
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
                validate(FootballCompetitionValidate::class)->check($param);
            } catch (ValidateException $e) {
                // 验证失败 输出错误信息
                return to_assign(1, $e->getError());
            }
			if(isset($param["updated_at"])){
					$param["updated_at"]= $param["updated_at"]?strtotime($param["updated_at"]):0;
				}
				
            $this->model->addFootballCompetition($param);
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
                validate(FootballCompetitionValidate::class)->check($param);
            } catch (ValidateException $e) {
                // 验证失败 输出错误信息
                return to_assign(1, $e->getError());
            }
			if(isset($param["updated_at"])){
					$param["updated_at"]= $param["updated_at"]?strtotime($param["updated_at"]):0;
				}
				
            $this->model->editFootballCompetition($param);
        }else{
			$id = isset($param['id']) ? $param['id'] : 0;
			$detail = $this->model->getFootballCompetitionById($id);
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
		$detail = $this->model->getFootballCompetitionById($id);
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

        $this->model->delFootballCompetitionById($id,$type);
   }
    /**
     * 同步数据
     */
    public function sync(){
        $this->model->sync();
    }
}
