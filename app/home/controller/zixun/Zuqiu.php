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
        $param = get_params();

        $teamid = $param['aid'] ?? 0;

        $this->tdk = new Tdk();

        if ($teamid > 0){
            $this->getCompInfo($teamid);
        }else{
            $this->getArticleList($param);
        }
        return View::fetch($this->tempPath);
    }

    protected function getCompInfo($matchId)
    {

        $this->getTempPath('zixun_zuqiu_detail');

        $this->getTdk('zixun_zuqiu_detail',$this->tdk);
        $info = Article::where(['id'=>$matchId])->find()->toArray();
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

    protected function getArticleList($param)
    {
        $param['page'] = isset($param['page'])?$param['page']:1;
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

        //echo "<pre>";
        foreach ($list['data'] as $k=>$v){
            $list['data'][$k]['short_name_zh'] = '';
            $list['data'][$k]['short_name_py'] = $v['cate_id']=='1'?'zuqiu':'lanqiu';
            $competition = $model->getArticleCompetition($v["id"]);
//            print_r($v);
//            print_r($competition);
            if($competition){
                $list['data'][$k]['short_name_zh'] =$competition['short_name_zh'] ;
                $list['data'][$k]['short_name_py'] =$competition['short_name_py'] ;
            }
        }

        //print_r($list);
        //exit;

        View::assign("list",$list);
        View::assign('param',$param);
        $this->getTempPath('zixun_zuqiu');
    }
}