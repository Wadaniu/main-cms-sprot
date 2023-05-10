<?php
/**
 * @copyright Copyright (c) 2021 勾股工作室
 * @license https://opensource.org/licenses/Apache-2.0
 * @link https://www.gougucms.com
 */

namespace app\admin\validate;
use think\Validate;

class FootballTeamValidate extends Validate
{
    protected $rule = [
    'competition_id' => 'require',
    'country_id' => 'require',
    'name_zh' => 'require',
    'name_zht' => 'require',
    'name_en' => 'require',
    'short_name_zh' => 'require',
    'logo' => 'require',
];

    protected $message = [
    'competition_id.require' => '赛事id不能为空',
    'country_id.require' => '国家id不能为空',
    'name_zh.require' => '中文名称不能为空',
    'name_zht.require' => '粤语名称不能为空',
    'name_en.require' => '英文名称不能为空',
    'short_name_zh.require' => '中文简称不能为空',
    'logo.require' => '球队logo不能为空',
];
}