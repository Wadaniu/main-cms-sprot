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

class Test extends BaseController
{

    /**
     * 新闻资讯
     */
    public function index(){
        echo "<pre>";
        //$list = getLuxiangJijin(2,1,10);
        $list = getZiXun(2,10);
        print_r($list);exit;
    }

}
