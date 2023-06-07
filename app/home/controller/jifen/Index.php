<?php

namespace app\home\controller\jifen;

use app\home\BaseController;
use think\facade\View;

class Index extends BaseController
{

    public function index(){

        View::assign('seo',['title'=>'','keywords'=>'','description'=>'']);
        return view('jifen/index');
    }
}