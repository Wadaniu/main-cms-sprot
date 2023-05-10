<?php
/**
 * @copyright Copyright (c) 2021 勾股工作室
 * @license https://opensource.org/licenses/Apache-2.0
 * @link https://www.gougucms.com
 */

namespace app\admin\validate;
use think\Validate;

class FootballMatchValidate extends Validate
{
    protected $rule = [
    'season_id' => 'require',
    'home_team_id' => 'require',
    'away_team_id' => 'require',
    'status_id' => 'require',
    'match_time' => 'require',
    'neutral' => 'require',
];

    protected $message = [
    'season_id.require' => '赛季id不能为空',
    'home_team_id.require' => '主队id不能为空',
    'away_team_id.require' => '客队id不能为空',
    'status_id.require' => '比赛状态不能为空',
    'match_time.require' => '比赛时间不能为空',
    'neutral.require' => '是否中立场不能为空',
];
}