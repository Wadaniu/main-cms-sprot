<?php
/**
 * @copyright Copyright (c) 2021 勾股工作室
 * @license https://opensource.org/licenses/Apache-2.0
 * @link https://www.gougucms.com
 */
namespace app\commonModel;
use think\facade\Db;
use think\model;

class Label extends Model
{

    public function getLabelList($where, $param)
    {
        $rows = empty($param['limit']) ? get_config('app.page_size') : $param['limit'];
        $order = empty($param['order']) ? 'id desc' : $param['order'];
        $list = self::where($where)
            ->order($order)
            ->paginate($rows, false, ['query' => $param])
            ->each(function ($item, $key) {

            });
        return $list;
    }
}

