<?php

namespace app\home\controller\jijin;

use app\home\BaseController;
use think\facade\View;

class Zuqiu extends BaseController
{
    public function index(){
        return View::fetch('/jijin/zuqiu/index');
    }

    public function matchInfo(){
        echo 234;die;
    }
}