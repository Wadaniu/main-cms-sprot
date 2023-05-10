<?php
/**
 * @copyright Copyright (c) 2021 勾股工作室
 * @license https://opensource.org/licenses/Apache-2.0
 * @link https://www.gougucms.com
 */

namespace app\admin\validate;
use think\Validate;

class FootballCountryValidate extends Validate
{
    protected $rule = [
    'category_id' => 'require',
    'name_zh' => 'require',
    'name_zht' => 'require',
    'name_en' => 'require',
    'logo' => 'require',
    'updated_at' => 'require',
];

    protected $message = [
    'category_id.require' => '分类id不能为空',
    'name_zh.require' => '中文名称不能为空',
    'name_zht.require' => '粤语名称不能为空',
    'name_en.require' => '英文名称不能为空',
    'logo.require' => '国家logo不能为空',
    'updated_at.require' => '更新时间不能为空',
];
}