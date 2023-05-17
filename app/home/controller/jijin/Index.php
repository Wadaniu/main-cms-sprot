<?php

namespace app\home\controller\jijin;

use app\home\BaseController;
use think\facade\View;

class Index extends BaseController
{
    public function index(){
        //获取模板路径
        return View::fetch('/jijin/index');
    }
}