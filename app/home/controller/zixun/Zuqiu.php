<?php

namespace app\home\controller\zixun;

use app\home\BaseController;
use app\home\Tdk;
use think\App;
use think\facade\View;
use app\commonModel\Article;
use app\commonModel\FootballCompetition;
use app\commonModel\Admin;
use app\commonModel\ArticleKeywords;

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

        $comp = Article::where('id',$matchId)->where("delete_time",0)->findOrEmpty();
        if ($comp->isEmpty()) {
            throw new \think\exception\HttpException(404, '找不到页面');
        }
        $this->getTempPath('zixun_zuqiu_detail');

        $info = $comp->toArray();
        $title = get_system_config("web",'title');
        $info['content'] = str_replace('JRS直播',$title,$info['content']);
        $info['desc'] = str_replace('JRS直播',$title,$info['desc']);

        $info['content'] = str_replace('直播吧',$title,$info['content']);
        $info['desc'] = str_replace('直播吧',$title,$info['desc']);
        $this->tdk->title = $info['title'];
        $this->tdk->keyword = $info['title'];
        $this->tdk->desc = $info['desc'];
        $this->tdk->short_name_zh = '足球';
        $competition = (new \app\commonModel\FootballCompetition())->getShortNameZh($comp->competition_id);
        if($competition){
            $this->tdk->short_name_zh = $competition['short_name_zh'];
        }
        $info['author'] = Admin::where(['id'=>$info['admin_id']])->find()->toArray();
        $info['pre'] = articlePrev($matchId);
        $info['next'] = articleNext($matchId);
        $articleKeyWords = ArticleKeywords::alias("a")
            ->field('a.*,b.title')->where("aid",$matchId)
            ->join("keywords b"," a.keywords_id=b.id")
            ->order("a.id desc")
            ->limit(5)
            ->select();
        ;

        View::assign('keywords',$articleKeyWords);
        $this->getTdk('zixun_zuqiu_detail',$this->tdk);
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

        $this->tdk->short_name_zh = '足球';
        $model = new Article();
        $cateIds = (new \app\commonModel\ArticleCate())->getFootCate();
        if(isset($param['compname']) && $param['compname']){
            $competition = FootballCompetition::where("short_name_py",$param['compname'])->find();
            if($competition){
                $list = $model->getArticleDatalist(['cate_id'=>$cateIds,'status'=>1,'delete_time'=>0,'competition_id'=>$competition->id],$param);
                $this->tdk->short_name_zh = $competition->short_name_zh;
            }else{
                $list = $model->getArticleDatalist(['cate_id'=>$cateIds,'status'=>1,'delete_time'=>0,],$param);
            }
            View::assign('comp',$competition);
        }else{
            $list = $model->getArticleDatalist(['cate_id'=>$cateIds,'status'=>1,'delete_time'=>0],$param);
        }
        $title = get_system_config("web",'title');
        foreach ($list['data'] as $k=>$v){
            $list['data'][$k]['short_name_zh'] = '';
            $list['data'][$k]['short_name_py'] = 'zuqiu';
            $list['data'][$k]['cate_id'] = 1;
            $competition = $model->getArticleCompetition($v);
            if($competition){
                $list['data'][$k]['short_name_zh'] =$competition['short_name_zh'] ;
                $list['data'][$k]['short_name_py'] =$competition['short_name_py'] ;
            }
            $list['data'][$k]['desc'] = str_replace('JRS直播',$title,$v['desc']);
            $list['data'][$k]['desc'] = str_replace('直播吧',$title,$list['data'][$k]['desc']);
        }

        //print_r($list);
        //exit;
        //$list['current_page'] = $param['page'];
        $this->getTdk('zixun_zuqiu',$this->tdk);
        View::assign("list",$list);
        View::assign('param',$param);
        $this->getTempPath('zixun_zuqiu');
    }
}