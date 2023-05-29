<?php

namespace app\home\controller\luxiang;

use app\home\BaseController;
use think\facade\View;
use think\App;
use app\home\Tdk;
use app\commonModel\MatchVedio;

class Index extends BaseController
{

    const RouteTag  = 'luxiang';

    public function __construct(App $app)
    {
        parent::__construct($app);
    }

    public function index(){
        //处理tdk
        $tdk = new Tdk();
        $this->getTdk(self::RouteTag,$tdk);
        $this->getTempPath(self::RouteTag);
        $list = (new MatchVedio())->getList(['type'=>2],["order"=>'match_id desc']);
        foreach ($list['data'] as $k=>$v){
            $list['data'][$k]['date']='';
            $list['data'][$k]['team']=[];
            $titleArr = explode(" ",$v['title']);
            if(preg_match("/月/",$titleArr[1])){
                $list['data'][$k]['date'] = str_replace("日","",str_replace("月","-",$titleArr[1]));
            }
            $list['data'][$k]['team'] = explode("vs",$titleArr[3]);
        }
        //var_dump($list);die;
        View::assign("list",$list);
        View::assign("index","录像");
        return View::fetch($this->tempPath);
    }

}