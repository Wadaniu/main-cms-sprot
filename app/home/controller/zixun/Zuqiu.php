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
        $info['pre'] = Article::where("id","<",$matchId)->order("id desc")->find();
        $info['next'] = Article::where("id",">",$matchId)->order("id asc")->find();
        View::assign("info",$info);
    }

    protected function getArticleList($compName)
    {
        $this->getTempPath('zixun_zuqiu');
        $this->getTdk('zixun_zuqiu',$this->tdk);

        $list = (new Article())->getArticleDatalist(['cate_id'=>1],[]);
        foreach ($list['data'] as $k=>$v){
                $comp = FootballCompetition::where(['id'=>$v['competition_id']])->find();
                if($comp){
                    $list['data'][$k]['comp'] = $comp->toArray();
                }else{
                    $list['data'][$k]['comp'] = [];
                }
        }
        View::assign("list",$list);
    }
}