<?php
/**
 * @copyright Copyright (c) 2021 勾股工作室
 * @license https://opensource.org/licenses/Apache-2.0
 * @link https://www.gougucms.com
 */

namespace app\admin\model;

use think\Model;

class SphereQueryUpdate extends Model
{
    protected $connection = 'compDataDb';

/*CREATE TABLE `sphere_query_update` (
`id` char(32) CHARACTER SET utf8mb4 NOT NULL COMMENT '主键',
`min_id` int(10) unsigned DEFAULT '1' COMMENT '数据最小id',
`max_id` int(10) unsigned DEFAULT '1' COMMENT '数据最大id',
`min_time` int(10) unsigned DEFAULT '1' COMMENT '最小time(更新时间戳)',
`max_time` int(10) unsigned DEFAULT '1' COMMENT '数据最大time(更新时间戳)',
`update_field` varchar(16) COLLATE utf8mb4_bin DEFAULT NULL COMMENT '更新字段',
`update_id` int(10) unsigned DEFAULT '0' COMMENT '更新ID日期',
`update_time` int(10) unsigned DEFAULT '0' COMMENT '更新时间',
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;*/

}
