<?php
/**
 * @copyright Copyright (c) 2021 勾股工作室
 * @license https://opensource.org/licenses/Apache-2.0
 * @link https://www.gougucms.com
 */
 
declare (strict_types = 1);

namespace app\home\controller;

use app\commonModel\Article as ArticleModel;
use app\home\BaseController;
use think\facade\View;
use app\commonModel\MatchVedio;
use app\commonModel\FootballTeam;

class Test extends BaseController
{

    /**
     * 新闻资讯
     */
    public function index(){
        echo "<pre>";
        $list = getZiXun(0,5,0);
        print_r($list);exit;
    }

}