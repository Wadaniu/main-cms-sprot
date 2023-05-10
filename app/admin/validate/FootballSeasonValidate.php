<?php
/**
 * @copyright Copyright (c) 2021 勾股工作室
 * @license https://opensource.org/licenses/Apache-2.0
 * @link https://www.gougucms.com
 */

namespace app\admin\validate;
use think\Validate;

class FootballSeasonValidate extends Validate
{
    protected $rule = [
    'competition_id' => 'require',
    'year' => 'require',
    'is_current' => 'require',
    'has_player_stats' => 'require',
    'has_team_stats' => 'require',
    'has_table' => 'require',
    'start_time' => 'require',
    'end_time' => 'require',
    'updated_at' => 'require',
];

    protected $message = [
    'competition_id.require' => '赛事id不能为空',
    'year.require' => '赛事年份不能为空',
    'is_current.require' => '是否最新赛季不能为空',
    'has_player_stats.require' => '是否有球员统计不能为空',
    'has_team_stats.require' => '是否有球队统计不能为空',
    'has_table.require' => '是否有积分榜不能为空',
    'start_time.require' => '开始时间不能为空',
    'end_time.require' => '结束时间不能为空',
    'updated_at.require' => '更新时间不能为空',
];
}