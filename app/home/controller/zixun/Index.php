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
        $tdk = new Tdk();
        $this->getTdk(self::RouteTag,$tdk);
        $this->getTempPath(self::RouteTag);
        $list = (new Article())->getArticleDatalist(['status'=>1,'delete_time'=>0],[]);
        foreach ($list['data'] as $k=>$v){
            if($v['cate_id']==1){//足球
                $comp = FootballCompetition::where(['id'=>$v['competition_id']])->find();
                if($comp){
                    $list['data'][$k]['comp'] = $comp->toArray();
                }else{
                    $list['data'][$k]['comp'] = [];
                }
            }else{// 篮球
                $comp = BasketballCompetition::where(['id'=>$v['competition_id']])->find();
                if($comp){
                    $list['data'][$k]['comp'] = $comp->toArray();
                }else{
                    $list['data'][$k]['comp'] = [];
                }

            }
        }
        View::assign("list",$list);
        return View::fetch($this->tempPath);
    }
}