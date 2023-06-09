<?php

namespace app\home\controller\zixun;

use app\home\BaseController;
use app\home\Tdk;
use think\App;
use think\facade\View;
use app\commonModel\Article;
use app\commonModel\FootballCompetition;
use app\commonModel\Admin;

class Zuqiu extends BaseController
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
            $this->getArticleList();
        }
        return View::fetch($this->tempPath);
    }

    protected function getCompInfo($matchId)
    {

        $comp = Article::where('id',$matchId)->findOrEmpty();
        if ($comp->isEmpty()) {
            throw new \think\exception\HttpException(404, '找不到页面');
        }
        $this->getTempPath('zixun_zuqiu_detail');
        $this->getTdk('zixun_zuqiu_detail',$this->tdk);
        $info = $comp->toArray();
        $this->tdk->title = $info['title'];
        $this->tdk->keyword = $info['title'];
        $this->tdk->desc = $info['desc'];
        $info['author'] = Admin::where(['id'=>$info['admin_id']])->find()->toArray();
        $info['pre'] = articlePrev($matchId);
        $info['next'] = articleNext($matchId);
        View::assign('article',['data'=>getZiXun(1,$info['competition_id'])]);

        View::assign("info",$info);
        View::assign("comp",['id'=>$info['competition_id']]);
    }

    protected function getArticleList()
    {
        $param = $this->parmas;

        $param['page'] = isset($param['page'])?$param['page']:1;
        $param['limit'] = 10;
        //print_r($param);exit;
        $this->getTdk('zixun_zuqiu',$this->tdk);
        $model = new Article();
        if(isset($param['compname']) && $param['compname']){
            $competition = FootballCompetition::where("short_name_py",$param['compname'])->find();
            if($competition){
                $list = $model->getArticleDatalist(['cate_id'=>1,'status'=>1,'delete_time'=>0,'competition_id'=>$competition->id],$param);
            }else{
                $list = $model->getArticleDatalist(['cate_id'=>1,'status'=>1,'delete_time'=>0],$param);
            }
            View::assign('comp',$competition);
        }else{
            $list = $model->getArticleDatalist(['cate_id'=>1,'status'=>1,'delete_time'=>0],$param);
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

        //print_r($list);
        //exit;
        //$list['current_page'] = $param['page'];
        View::assign("list",$list);
        View::assign('param',$param);
        $this->getTempPath('zixun_zuqiu');
    }
}