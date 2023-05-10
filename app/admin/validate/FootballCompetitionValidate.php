<?php
/**
 * @copyright Copyright (c) 2021 勾股工作室
 * @license https://opensource.org/licenses/Apache-2.0
 * @link https://www.gougucms.com
 */

namespace app\admin\validate;
use think\Validate;

class FootballCompetitionValidate extends Validate
{
    protected $rule = [
    'category_id' => 'require',
    'country_id' => 'require',
    'name_zh' => 'require',
    'short_name_zh' => 'require',
    'updated_at' => 'require',
];

    protected $message = [
    'category_id.require' => '分类id不能为空',
    'country_id.require' => '分类id不能为空',
    'name_zh.require' => '中文名称不能为空',
    'short_name_zh.require' => '中文简称不能为空',
    'updated_at.require' => '更新时间不能为空',
];
}