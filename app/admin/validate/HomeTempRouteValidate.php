<?php
/**
 * @copyright Copyright (c) 2021 勾股工作室
 * @license https://opensource.org/licenses/Apache-2.0
 * @link https://www.gougucms.com
 */

namespace app\admin\validate;
use think\Validate;

class HomeTempRouteValidate extends Validate
{
    protected $rule = [
    'temp_name' => 'require',
    'temp_path' => 'require',
    'temp_AP' => 'require',
];

    protected $message = [
    'temp_name.require' => '模板名称不能为空',
    'temp_path.require' => '模板路径不能为空',
    'temp_AP.require' => '模板绝对路径不能为空',
];
}