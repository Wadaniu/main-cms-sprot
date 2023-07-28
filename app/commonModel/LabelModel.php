<?php
/**
 * @copyright Copyright (c) 2021 勾股工作室
 * @license https://opensource.org/licenses/Apache-2.0
 * @link https://www.gougucms.com
 */
namespace app\commonModel;
use think\facade\Db;
use think\model;

class LabelModel extends Model
{
    protected $name = 'label';
    protected $pk = 'id';

    public function getLabelList($where, $param)
    {
        $rows = empty($param['limit']) ? get_config('app.page_size') : $param['limit'];
        $order = empty($param['order']) ? 'id desc' : $param['order'];
        $list = self::where($where)
            ->order($order)
            ->paginate($rows, false, ['query' => $param])
            ->each(function ($item, $key) {
                $item->createdAt = $item->createdAt>0?date("Y-m-d H:i:s",$item->createdAt):'--';
            });
           ;
        return $list;
    }


    /**
     * 根据id获取信息
     * @param $id
     */
    public function getLabelById($id)
    {
        $info = $this->where('id', $id)->find();
        return $info;
    }


    public function postLabel(){

        $config = get_system_config("web");
        $ch = curl_init($config['labeldomain']);
        $data_json = json_encode(["url"=>$config["domain"],'id'=>$this->id]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
}

