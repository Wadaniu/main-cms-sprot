<?php

namespace app\home\controller\zixun;

use app\home\BaseController;
use app\home\Tdk;
use think\App;
use think\facade\View;
use app\commonModel\Article;
use app\commonModel\BasketballCompetition;
use app\commonModel\Admin;

class Lanqiu extends BaseController
{
    public function __construct(App $app)
    {
        parent::__construct($app);
    }
    public function index(){
        $param = $this->parmas;

        $teamid = $param['aid'] ?? 0;

        $this->tdk = new Tdk();

        if ($teamid > 0){
            $this->getCompInfo($teamid);
        }else{
            $this->getMatchList($param);
        }
        return View::fetch($this->tempPath);
    }

    protected function getCompInfo($matchId)
    {
        $comp = Article::where('id',$matchId)->findOrEmpty();
        if ($comp->isEmpty()) {
            throw new \think\exception\HttpException(404, '找不到页面');
        }
        $this->getTempPath('zixun_lanqiu_detail');

        $info = $comp->toArray();
        $this->tdk->title = $info['title'];
        $this->tdk->keyword = $info['title'];
        $this->tdk->desc = $info['desc'];
        $this->tdk->short_name_zh = '篮球';
        $competition = (new \app\commonModel\BasketballCompetition())->getShortNameZh($comp->competition_id);
        if($competition){
            $this->tdk->short_name_zh = $competition['short_name_zh'];
        }
        $info['author'] = Admin::where(['id'=>$info['admin_id']])->find()->toArray();
        $info['pre'] = articlePrev($matchId);
        $info['next'] = articleNext($matchId);
        $this->getTdk('zixun_lanqiu_detail',$this->tdk);
        View::assign('article',['data'=>getZiXun(2,$info['competition_id'])]);
        View::assign("info",$info);
        View::assign("comp",['id'=>$info['competition_id']]);
    }

    protected function getMatchList($param)
    {
        $param = $this->parmas;
        $param['page'] = isset($param['page'])?$param['page']:1;
        $param['limit'] = 10;
        $model = new Article();
        $cateIds = (new \app\commonModel\ArticleCate())->getBasketCate();
        $this->tdk->short_name_zh = '篮球';
        //$list = $model->getArticleDatalist(['cate_id'=>2,'status'=>1,'delete_time'=>0],[]);
        if(isset($param['compname']) && $param['compname']){
            $competition = BasketballCompetition::where("short_name_py",$param['compname'])->find();
            if($competition){
                $list = $model->getArticleDatalist(['cate_id'=>$cateIds,'status'=>1,'delete_time'=>0,'competition_id'=>$competition->id],$param);
                $this->tdk->short_name_zh = $competition->short_name_zh;
            }else{
                $list = $model->getArticleDatalist(['cate_id'=>$cateIds,'status'=>1,'delete_time'=>0],$param);
            }
            View::assign('comp',$competition);
        }else{
            $list = $model->getArticleDatalist(['cate_id'=>$cateIds,'status'=>1,'delete_time'=>0],$param);
        }



        foreach ($list['data'] as $k=>$v){
            $list['data'][$k]['short_name_zh'] = '';
            $list['data'][$k]['short_name_py'] = '';
            $competition = $model->getArticleCompetition($v);
            $list['data'][$k]['cate_id'] = 2;
            if($competition){
                $list['data'][$k]['short_name_zh'] =$competition['short_name_zh'] ;
                $list['data'][$k]['short_name_py'] =$competition['short_name_py'] ;
            }
        }
        //$list['current_page'] = $param['page'];
        $this->getTdk('zixun_lanqiu',$this->tdk);
        View::assign("list",$list);
        View::assign('param',$param);
        $this->getTempPath('zixun_lanqiu');
    }
}