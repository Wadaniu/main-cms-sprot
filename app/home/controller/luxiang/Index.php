<?php

namespace app\home\controller\luxiang;

use app\home\BaseController;
use think\facade\View;

class Index extends BaseController
{

    const RouteTag  = 'luxiang';
    private $tempPath;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $temp_id = $this->nav[self::RouteTag]['temp_id'];
        $temp_info = (new \app\commonModel\HomeTempRoute)->getHomeTempRouteById($temp_id);
        $this->tempPath = $temp_info->temp_path;
    }

    public function index(){
        //获取模板路径
        $titleTemp = $this->nav[self::RouteTag]['web_title'];

        return View::fetch($this->tempPath);
    }

}