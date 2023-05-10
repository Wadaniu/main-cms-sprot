<?php
/**
 * @copyright Copyright (c) 2021 勾股工作室
 * @license https://opensource.org/licenses/Apache-2.0
 * @link https://www.gougucms.com
 */

namespace app\admin\validate;
use think\Validate;

class BasketballTeamValidate extends Validate
{
    protected $rule = [
    'competition_id' => 'require',
    'conference_id' => 'require',
    'venue_id' => 'require',
    'name_zh' => 'require',
    'short_name_zh' => 'require',
];

    protected $message = [
    'competition_id.require' => '赛事id不能为空',
    'conference_id.require' => '赛区id不能为空',
    'venue_id.require' => '场馆id不能为空',
    'name_zh.require' => '中文名称不能为空',
    'short_name_zh.require' => '中文简称不能为空',
];
}