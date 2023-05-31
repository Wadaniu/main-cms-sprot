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
        $param = get_params();

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
        $this->getTempPath('zixun_lanqiu_detail');



        $this->getTdk('zixun_lanqiu_detail',$this->tdk);
        $info = Article::where(['id'=>$matchId])->find()->toArray();
        $this->tdk->title = $info['title'];
        $this->tdk->keyword = $info['title'];
        $this->tdk->desc = $info['desc'];
        $info['author'] = Admin::where(['id'=>$info['admin_id']])->find()->toArray();
        $info['pre'] = Article::where("id","<",$matchId)->order("id desc")->find();
        $info['next'] = Article::where("id",">",$matchId)->order("id asc")->find();
        View::assign("info",$info);
    }

    protected function getMatchList($param)
    {
        $param['page'] = isset($param['page'])?$param['page']:1;
        $model = new Article();
        $this->getTdk('zixun_lanqiu',$this->tdk);
        $list = $model->getArticleDatalist(['cate_id'=>2,'status'=>1,'delete_time'=>0],[]);
        foreach ($list['data'] as $k=>$v){
            $list['data'][$k]['short_name_zh'] = '';
            $list['data'][$k]['short_name_py'] = $v['cate_id']=='1'?'zuqiu':'lanqiu';
            $competition = $model->getArticleCompetition($v["id"]);
            if($competition){
                $list['data'][$k]['short_name_zh'] =$competition['short_name_zh'] ;
                $list['data'][$k]['short_name_py'] =$competition['short_name_py'] ;
            }
        }
        View::assign("list",$list);
        View::assign('param',$param);
        $this->getTempPath('zixun_lanqiu');
    }
}