<?php
/**
 * @copyright Copyright (c) 2021 勾股工作室
 * @license https://opensource.org/licenses/Apache-2.0
 * @link https://www.gougucms.com
 */

namespace app\admin\validate;
use think\Validate;

class MatchliveLinkValidate extends Validate
{
    protected $rule = [
    'title' => 'require',
    'live_link' => 'require',
    'status' => 'require',
];

    protected $message = [
    'title.require' => '直播名称不能为空',
    'live_link.require' => '直播地址不能为空',
    'status.require' => '1开启 0关闭不能为空',
];
}