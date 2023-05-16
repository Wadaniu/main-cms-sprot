<?php

namespace app\home\controller\jijin;

use app\home\BaseController;
use think\facade\View;

class Lanqiu extends BaseController
{
    public function index(){
        return View::fetch('/jijin/lanqiu/index');
    }
}