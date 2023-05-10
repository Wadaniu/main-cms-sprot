<?php
/**
 * @copyright Copyright (c) 2021 勾股工作室
 * @license https://opensource.org/licenses/Apache-2.0
 * @link https://www.gougucms.com
 */
 
declare (strict_types = 1);

namespace app\admin\controller;

use app\admin\BaseController;
use app\admin\model\Article as ArticleModel;
use app\admin\validate\ArticleValidate;
use think\exception\ValidateException;
use think\facade\Db;
use think\facade\View;

class Article extends BaseController

{
	/**
     * 构造函数
     */
    public function __construct()
    {
        $this->model = new ArticleModel();
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
                $where[] = ['a.id|a.title|a.desc|a.content|c.title', 'like', '%' . $param['keywords'] . '%'];
            }
            if (!empty($param['cate_id'])) {
                $where[] = ['a.cate_id', '=', $param['cate_id']];
            }
            $where[] = ['a.delete_time', '=', 0];
            $ArticleModel = new ArticleModel();
            $list = $ArticleModel->getArticleList($where, $param);
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
			if (isset($param['table-align'])) {
				unset($param['table-align']);
			}
			if (isset($param['content'])) {
				$param['md_content'] = '';
			}
			if (isset($param['docContent-html-code'])) {
				$param['content'] = $param['docContent-html-code'];
				$param['md_content'] = $param['docContent-markdown-doc'];
				unset($param['docContent-html-code']);
				unset($param['docContent-markdown-doc']);
			}
			$param['admin_id'] = $this->uid;
            // 检验完整性
            try {
                validate(ArticleValidate::class)->check($param);
            } catch (ValidateException $e) {
                // 验证失败 输出错误信息
                return to_assign(1, $e->getError());
            }

            //随机阅读量
            $param['read'] = rand(1000,2000);
            //作者，默认网站名称
            $param['origin'] = $param['origin'] ?? get_system_config('web','title');
            //截取前100个字做简介
            $param['desc'] = @msubstr(checkStrHtml($param['content']), 0, 100, false);
            //截取第一个图片做缩略图
            $param['origin_url'] = get_html_first_imgurl($param['content']);

			$ArticleModel = new ArticleModel();	
            $ArticleModel->addArticle($param);
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
		$param = get_params();
        $ArticleModel = new ArticleModel();
		
        if (request()->isAjax()) {		
			if (isset($param['table-align'])) {
				unset($param['table-align']);
			}
			if (isset($param['content'])) {
				$param['md_content'] = '';
			}
			if (isset($param['docContent-html-code'])) {
				$param['content'] = $param['docContent-html-code'];
				$param['md_content'] = $param['docContent-markdown-doc'];
				unset($param['docContent-html-code']);
				unset($param['docContent-markdown-doc']);
			}
            if(empty($param['jijin_time'])){
                $param['jijin_time'] = time();
            }else{
                $param['jijin_time'] = strtotime($param['jijin_time']);
            }
            // 检验完整性
            try {
                validate(ArticleValidate::class)->check($param);
            } catch (ValidateException $e) {
                // 验证失败 输出错误信息
                return to_assign(1, $e->getError());
            }

            //截取前100个字做简介
            $param['desc'] = @msubstr(checkStrHtml($param['content']), 0, 100, false);
            //截取第一个图片做缩略图
            $param['origin_url'] = get_html_first_imgurl($param['content']);
            $ArticleModel->editArticle($param);
        }else{
			$id = isset($param['id']) ? $param['id'] : 0;
			$detail = $ArticleModel->getArticleById($id);
			View::assign('editor', get_system_config('other','editor'));
			if (!empty($detail)) {
				if(!empty($article['md_content'])){
                    View::assign('editor',1);
                }
				$keyword_array = Db::name('ArticleKeywords')
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
    }


    /**
    * 查看信息
    */
    public function read()
    {
        $param = get_params();
		$id = isset($param['id']) ? $param['id'] : 0;
		$ArticleModel = new ArticleModel();
		$detail = $ArticleModel->getArticleById($id);
		if (!empty($detail)) {  
			$keyword_array = Db::name('ArticleKeywords')
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

        $ArticleModel = new ArticleModel();
        $ArticleModel->delArticleById($id,$type);
   }
}
