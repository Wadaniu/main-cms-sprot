<?php

namespace app\home\controller\jijin;

use app\home\BaseController;
use think\App;
use think\facade\View;
use app\home\Tdk;
use app\commonModel\MatchVedio;
use app\commonModel\FootballTeam;
use app\commonModel\BasketballTeam;

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
        list($list,$competition_id,$param)=getMatchVedio(['type'=>1]);
        View::assign("href","");
        View::assign("compName",'');
        View::assign("list",$list);
        View::assign("index","集锦");
        View::assign("compName",'');
        View::assign("param",$param);
        View::assign("luxiang",getLuxiangJijin(1,'',$competition_id,4));
        return View::fetch($this->tempPath);

    }
}