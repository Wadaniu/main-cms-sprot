<?php

namespace app\home\controller\live;

use app\home\BaseController;
use app\home\Tdk;
use think\App;
use think\facade\View;

class Index extends BaseController
{
    const RouteTag  = 'live';
    private $tempPath;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $temp_id = $this->nav[self::RouteTag]['temp_id'];
        $temp_info = (new \app\commonModel\HomeTempRoute)->getHomeTempRouteById($temp_id);
        $this->tempPath = $temp_info->temp_path;
    }
    public function index(){

        $basketballComp = getBasketballHotComp();
        $footballComp = getFootballHotComp();

        $hotFootballId = array_column($footballComp,'id');
        $hotBasketballId = array_column($basketballComp,'id');

        //获取热门联赛X天内数据


        //处理tdk
        $tdk = new Tdk();
        $seo['title'] = $this->replaceTDK($this->nav[self::RouteTag]['web_title'],$tdk);
        $seo['keywords'] = $this->replaceTDK($this->nav[self::RouteTag]['web_keywords'],$tdk);
        $seo['description'] = $this->replaceTDK($this->nav[self::RouteTag]['web_desc'],$tdk);

        View::assign('seo',$seo);
        return View::fetch($this->tempPath);
    }
}