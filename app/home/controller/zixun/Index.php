<?php

namespace app\home\controller\zixun;

use app\home\BaseController;
use app\home\Tdk;
use think\App;
use think\facade\View;
use app\commonModel\Article;
use app\commonModel\FootballCompetition;
use app\commonModel\BasketballCompetition;

class Index extends BaseController
{
    const RouteTag  = 'zixun';

    public function __construct(App $app)
    {
        parent::__construct($app);

    }
    public function index(){
        $param = $this->parmas;
        $param['page'] = isset($param['page'])?$param['page']:1;
        $param['limit'] = 10;
        $this->tdk = new Tdk();

        $this->getTempPath(self::RouteTag);
        $model = new Article();
        $this->tdk->keyword = '';
        if(isset($param['keywords_id']) && $param['keywords_id']){
            $aid = \app\commonModel\ArticleKeywords::where("keywords_id",$param['keywords_id'])->column('aid');
            $list = $model->getArticleDatalist(['status'=>1,'delete_time'=>0,'id'=>$aid],$param);
            $this->tdk->keyword = (new \app\commonModel\Keywords())->where("id",$param['keywords_id'])->value('title');
        }else{
            $list = $model->getArticleDatalist(['status'=>1,'delete_time'=>0],$param);
        }

        foreach ($list['data'] as $k=>$v){
            $list['data'][$k]['short_name_zh'] = '';
            $list['data'][$k]['short_name_py'] = $v['cate_id']=='1'?'zuqiu':'lanqiu';
            $competition = $model->getArticleCompetition($v);
            if($competition){
                $list['data'][$k]['short_name_zh'] =$competition['short_name_zh'] ;
                $list['data'][$k]['short_name_py'] =$competition['short_name_py'] ;
            }
        }
        $this->getTdk(self::RouteTag,$this->tdk);
        //print_r($param);exit;
        View::assign("list",$list);
        View::assign('param',$param);
        return View::fetch($this->tempPath);
    }
}