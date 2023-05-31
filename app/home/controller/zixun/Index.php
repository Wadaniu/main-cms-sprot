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
        $param = get_params();
        $param['page'] = isset($param['page'])?$param['page']:1;
        //$param['limit'] = 1;
        $tdk = new Tdk();
        $this->getTdk(self::RouteTag,$tdk);
        $this->getTempPath(self::RouteTag);
        $model = new Article();
        $list = $model->getArticleDatalist(['status'=>1,'delete_time'=>0],$param);
        foreach ($list['data'] as $k=>$v){
            $list['data'][$k]['short_name_zh'] = '';
            $list['data'][$k]['short_name_py'] = $v['cate_id']=='1'?'zuqiu':'lanqiu';
            $competition = $model->getArticleCompetition($v["id"]);
            if($competition){
                $list['data'][$k]['short_name_zh'] =$competition['short_name_zh'] ;
                $list['data'][$k]['short_name_py'] =$competition['short_name_py'] ;
            }
        }
        //print_r($param);exit;
        View::assign("list",$list);
        View::assign('param',$param);
        return View::fetch($this->tempPath);
    }
}