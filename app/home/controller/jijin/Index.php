<?php

namespace app\home\controller\jijin;

use app\home\BaseController;
use think\facade\View;

class Index extends BaseController
{
    public function index(){
        //var_dump(getHomeRule());die;
        return View::fetch('/jijin/index');
    }
}