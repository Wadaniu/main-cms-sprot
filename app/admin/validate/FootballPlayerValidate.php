<?php
/**
 * @copyright Copyright (c) 2021 勾股工作室
 * @license https://opensource.org/licenses/Apache-2.0
 * @link https://www.gougucms.com
 */

namespace app\admin\validate;
use think\Validate;

class FootballPlayerValidate extends Validate
{
    protected $rule = [
    'logo' => 'require',
    'country_id' => 'require',
    'nationality' => 'require',
    'age' => 'require',
    'contract_until' => 'require',
    'preferred_foot' => 'require',
];

    protected $message = [
    'logo.require' => '球员logo不能为空',
    'country_id.require' => '国家id不能为空',
    'nationality.require' => '国籍不能为空',
    'age.require' => '年龄不能为空',
    'contract_until.require' => '合同截止时间不能为空',
    'preferred_foot.require' => '惯用脚不能为空',
];
}