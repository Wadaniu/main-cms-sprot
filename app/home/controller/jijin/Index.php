<?php

namespace app\home\controller\jijin;

use app\home\BaseController;
use think\App;
use think\facade\View;
use app\home\Tdk;
use app\commonModel\MatchVedio;

class Index extends BaseController
{
    const RouteTag  = 'jijin';

    public function __construct(App $app)
    {
        parent::__construct($app);
    }

    public function index(){
        $tdk = new Tdk();
        $this->getTdk(self::RouteTag,$tdk);
        $this->getTempPath(self::RouteTag);
        $list = (new MatchVedio())->getList(['type'=>1],["order"=>'match_id desc']);
        foreach ($list['data'] as $k=>$v){
            $list['data'][$k]['date']='';
            $list['data'][$k]['team']=[];
            $titleArr = explode(" ",$v['title']);
            if(preg_match("/月/",$titleArr[1])){
                $list['data'][$k]['date'] = str_replace("日","",str_replace("月","-",$titleArr[1]));
            }
            if(isset($titleArr[3])){
                $list['data'][$k]['team'] = explode("vs",$titleArr[3]);
            }
        }
        View::assign("list",$list);
        View::assign("index","集锦");
        return View::fetch($this->tempPath);

    }
}