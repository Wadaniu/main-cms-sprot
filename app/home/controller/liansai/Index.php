<?php

namespace app\home\controller\liansai;

use app\home\BaseController;
use think\facade\View;

class Index extends BaseController
{
    public function index(){
        return View::fetch('/jijin/index');
    }
}