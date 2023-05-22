<?php
/**
 * @copyright Copyright (c) 2021 勾股工作室
 * @license https://opensource.org/licenses/Apache-2.0
 * @link https://www.gougucms.com
 */

declare (strict_types = 1);

namespace app\home\controller;

use app\home\BaseController;
use think\facade\Cache;
use think\facade\Env;
use think\facade\View;

class Index extends BaseController
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::initialize();
    }


    public function index(): \think\response\View
    {
        return View('');
    }

}
