<?php
/**
 * @copyright Copyright (c) 2021 勾股工作室
 * @license https://opensource.org/licenses/Apache-2.0
 * @link https://www.gougucms.com
 */

namespace app\admin\validate;
use think\Validate;

class BasketballCountryValidate extends Validate
{
    protected $rule = [
    'category_id' => 'require',
    'name_zh' => 'require',
];

    protected $message = [
    'category_id.require' => '分类id不能为空',
    'name_zh.require' => '中文名称不能为空',
];
}