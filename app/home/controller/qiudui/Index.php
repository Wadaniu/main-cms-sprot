<?php

namespace app\home\controller\qiudui;

use app\home\BaseController;
use think\facade\View;

class Index extends BaseController
{
    public function index(){
        return View::fetch('/jijin/index');
    }
}