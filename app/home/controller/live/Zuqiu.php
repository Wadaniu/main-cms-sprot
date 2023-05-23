<?php

namespace app\home\controller\live;

use app\home\BaseController;
use app\home\Tdk;
use think\App;
use think\facade\View;

class Zuqiu extends BaseController
{
    const RouteTag  = 'live_zuqiu';
    private $tempPath;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $temp_id = $this->nav[self::RouteTag]['temp_id'];
        $temp_info = (new \app\commonModel\HomeTempRoute)->getHomeTempRouteById($temp_id);
        $this->tempPath = $temp_info->temp_path;
    }
    public function index(){
        //获取x天内

        //处理tdk
        $tdk = new Tdk();
        $seo['title'] = $this->replaceTDK($this->nav[self::RouteTag]['web_title'],$tdk);
        $seo['keywords'] = $this->replaceTDK($this->nav[self::RouteTag]['web_keywords'],$tdk);
        $seo['description'] = $this->replaceTDK($this->nav[self::RouteTag]['web_desc'],$tdk);

        View::assign('seo',$seo);
        return View::fetch($this->tempPath);
    }
}