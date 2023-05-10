<?php

namespace app\api\controller;
use app\admin\model\ArticleCate;
use app\api\BaseController;
use think\facade\Request;
use app\admin\model\Article AS ArticleModel;

class Article extends BaseController
{
    public $cateModel;

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::initialize();
        $this->model = new ArticleModel();
        $this->cateModel = new ArticleCate();
    }

    public function getByType(){
        $type = Request::get('type', 0);
        try {
            //获取分类id下所有子集
            $types = $this->cateModel->getCateAllById($type);


            $where = [
                'cate_id','IN',$types
            ];
            $data = $this->model->getArticleList($where,[]);
            $this->apiSuccess('请求成功', ['list' => $data]);
        }catch (Exception $e){
            $this->apiError($e->getMessage());
        }
    }

    public function getByCompId(){
        $compId = Request::get('id', 0);
        try {
            $where = 'a.competition_id = '.$compId;
            $data = $this->model->getListByCompId($where,[]);
            $this->apiSuccess('请求成功', $data);
        }catch (Exception $e){
            $this->apiError($e->getMessage());
        }
    }

    public function getByAid(){
        $aId = Request::get('id', 0);
        try {
            $data = $this->model->getArticleById($aId);
            $this->apiSuccess('请求成功', $data);
        }catch (Exception $e){
            $this->apiError($e->getMessage());
        }
    }
}