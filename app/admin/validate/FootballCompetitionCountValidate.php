<?php
/**
 * @copyright Copyright (c) 2021 勾股工作室
 * @license https://opensource.org/licenses/Apache-2.0
 * @link https://www.gougucms.com
 */

namespace app\admin\validate;
use think\Validate;

class FootballCompetitionCountValidate extends Validate
{
    protected $rule = [
    'team_id' => 'require',
    'points' => 'require',
    'position' => 'require',
    'deduct_points' => 'require',
    'note' => 'require',
    'total' => 'require',
    'won' => 'require',
    'draw' => 'require',
    'loss' => 'require',
    'goals' => 'require',
    'goals_against' => 'require',
    'goal_diff' => 'require',
    'updated_at' => 'require',
];

    protected $message = [
    'team_id.require' => '球队id不能为空',
    'points.require' => '积分不能为空',
    'position.require' => '排名不能为空',
    'deduct_points.require' => '扣除积分不能为空',
    'note.require' => '赛季年份不能为空',
    'total.require' => '比赛场次不能为空',
    'won.require' => '胜的场次不能为空',
    'draw.require' => '平的场次不能为空',
    'loss.require' => '负的场次不能为空',
    'goals.require' => '进球不能为空',
    'goals_against.require' => '失球不能为空',
    'goal_diff.require' => '净胜球不能为空',
    'updated_at.require' => '更新时间	不能为空',
];
}