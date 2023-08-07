<?php
/**
 * @copyright Copyright (c) 2021 勾股工作室
 * @license https://opensource.org/licenses/Apache-2.0
 * @link https://www.gougucms.com
 */
 
declare (strict_types = 1);

namespace app\admin\controller;

use app\admin\BaseController;
use app\admin\validate\ArticleCateValidate;
use app\commonModel\ArticleCate as ArticleCateModel;
use think\exception\ValidateException;
use think\facade\Db;
use think\facade\View;
use \app\commonModel\FootballCompetition;
use \app\commonModel\BasketballCompetition;

class ArticleCate extends BaseController

{
	/**
     * 构造函数
     */
    public function __construct()
    {

        $this->model = new ArticleCateModel();
    }
    /**
    * 数据列表
    */
    public function datalist()
    {
        if (request()->isAjax()) {
            $list = Db::table("fb_article_cate")
                ->select()
                ->toArray()
            ;
            $foot = new FootballCompetition();
            $bas = new BasketballCompetition();
            $footcate = (new \app\commonModel\ArticleCate())->where("pid",1)->column("id");
            $footcate[] = 1;
            foreach ($list as &$v){
                $v['competition_title'] = '';
                if(in_array($v['id'],$footcate)){
                    $football = $foot->getShortNameZh($v['competition_id']);
                    if($football){
                        $v['competition_title']="足球:".$football['name_zh']."（简称：".$football['short_name_zh']."）";
                    }
                }else{
                    $basketball = $bas->getShortNameZh($v['competition_id']);
                    if($basketball){
                        $v['competition_title']="篮球:".$basketball['name_zh']."（简称：".$basketball['short_name_zh']."）";
                    }
                }
            }
            return to_assign(0, '', $list);
        }
        else{
            return view();
        }
    }
	
	//获取子分类id.$is_self=1包含自己
	public function get_cate_son($id = 0, $is_self = 1)
	{
		$cates = $this->model->where('delete_time',0)->order('sort asc')->select()->toArray();
		$cates_list = get_data_node($cates, $id);
		$cates_array = array_column($cates_list, 'id');
		if ($is_self == 1) {
			//包括自己在内
			$cates_array[] = $id;
		}
		return $cates_array;
	}

    /**
    * 添加
    */
    public function add()
    {
		$param = get_params();	
        if (request()->isAjax()) {	
			
            // 检验完整性
            try {
                validate(ArticleCateValidate::class)->check($param);
            } catch (ValidateException $e) {
                // 验证失败 输出错误信息
                return to_assign(1, $e->getError());
            }			
            $this->model->addArticleCate($param);
        }else{
			$pid = isset($param['pid']) ? $param['pid'] : 0;
			View::assign('pid', $pid);
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
                validate(ArticleCateValidate::class)->check($param);
            } catch (ValidateException $e) {
                // 验证失败 输出错误信息
                return to_assign(1, $e->getError());
            }
			$cate_array = $this->get_cate_son($param['id']);
            if (in_array($param['pid'], $cate_array)) {
				return to_assign(1, '上级分类不能是该分类本身或其子分类');
			}
            $this->model->editArticleCate($param);
        }else{
			$id = isset($param['id']) ? $param['id'] : 0;
			$detail = $this->model->getArticleCateById($id);
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
		$detail = $this->model->getArticleCateById($id);
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
		$type = isset($param['type']) ? $param['type'] : 0;
		$count_cate = Db::name('ArticleCate')->where(["pid"=>$id,"delete_time"=>0])->count();
        if ($count_cate > 0) {
            return to_assign(1, "该分类下还有子分类，无法删除");
        }
        $count_article = Db::name('Article')->where(["cate_id" => $id,"delete_time"=>0])->count();
        if ($count_article > 0) {
            return to_assign(1, "该分类下还有文章，无法删除");
        }
        $this->model->delArticleCateById($id,$type);
   }
}
