<?php
/**
 * @copyright Copyright (c) 2021 勾股工作室
 * @license https://opensource.org/licenses/Apache-2.0
 * @link https://www.gougucms.com
 */
 
declare (strict_types = 1);

namespace app\home\controller;

use app\commonModel\Article as ArticleModel;
use app\home\BaseController;
use think\facade\View;

class Article extends BaseController
{

    /**
     * 新闻资讯
     */
    public function index(){
        $param = get_params();
        $type = $param['type'];
        $where = array();
        $param = $this->request->param();
        if($type=="basketball"){
            $where[] = ["cate_id","=",3];
        }else{
            $where[] = ["cate_id","=",4];
        }
        if(!empty($param["cid"])){
            $where[] = ["competition_id","=",$param["cid"]];
        }
        $model = new ArticleModel();
        $voList = $model->getArticleList($where,$param);
        $_GET['type'] = $type;
        View::assign("voList",$voList);
        return view();
    }

    public function detail()
    {
        $param = get_params();
        $type = $param['type'];
        $_GET['type'] = $type;
        $param = get_params();
        $id = isset($param['id']) ? $param['id'] : 0;
        $detail = get_article_detail($id);
        ArticleModel::where('id', $param['id'])->inc('read')->update();
        View::assign('detail', $detail);
		return view();
    }



    public function jijin(){
        $param = get_params();
        $where = array();
        $type = $param['type'];
        if($type=="basketball"){
            $where[] = ["cate_id","=",5];
        }else{
            $where[] = ["cate_id","=",6];
        }
        if(!empty($param["cid"])){
            $where[] = ["competition_id","=",$param["cid"]];
        }
        $_GET['type'] = $type;
        $model = new ArticleModel();
        $voList = $model->getArticleList($where,$param);
        View::assign("voList",$voList);
        View::assign("type",$type);
        return view();
    }

    /**
     * @return \think\response\View
     */
    public function jijindetail(){
        $param = get_params();
        $type = $param['type'];
        $_GET['type'] = $type;
        $param = get_params();
        $id = isset($param['id']) ? $param['id'] : 0;
        $detail = get_article_detail($id);
        ArticleModel::where('id', $param['id'])->inc('read')->update();
        View::assign('detail', $detail);
        return view();
    }


    /**
     * @param array $cateId
     * @param $competition_id
     */
    protected function getList($cateId=[],$competition_id){
        $model = new ArticleModel();

        $model->getArticleList();
    }

}
