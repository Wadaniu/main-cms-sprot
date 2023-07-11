<?php
/**
 * @copyright Copyright (c) 2021 勾股工作室
 * @license https://opensource.org/licenses/Apache-2.0
 * @link https://www.gougucms.com
 */

declare (strict_types = 1);

namespace app\admin\controller;

use app\admin\BaseController;
use app\admin\validate\KeywordsCheck;
use think\exception\ValidateException;
use think\facade\Db;
use think\facade\View;
use PHPExcel;
use PHPExcel_IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use \app\commonModel\Keywords as model;

class Keywords extends BaseController
{
    public function index()
    {
        if (request()->isAjax()) {
            $param = get_params();
            $where = array();
            if (!empty($param['keywords'])) {
                $where[] = ['title', 'like', '%' . $param['keywords'] . '%'];
            }
            $where[] = ['status', '>=', 0];
            $rows = empty($param['limit']) ? get_config('app.page_size') : $param['limit'];
            $content = Db::name('Keywords')
                ->order('id desc')
                ->where($where)
                ->paginate($rows, false, ['query' => $param]);
            return table_assign(0, '', $content);
        } else {
            return view();
        }
    }

    //添加
    public function add()
    {
        $param = get_params();
        if (request()->isAjax()) {
            if (!empty($param['id']) && $param['id'] > 0) {
                try {
                    validate(KeywordsCheck::class)->scene('edit')->check($param);
                } catch (ValidateException $e) {
                    // 验证失败 输出错误信息
                    return to_assign(1, $e->getError());
                }
                $param['update_time'] = time();
                $res = Db::name('Keywords')->strict(false)->field(true)->update($param);
                if ($res) {
                    add_log('edit', $param['id'], $param);
                }

                return to_assign();
            } else {
                try {
                    validate(KeywordsCheck::class)->scene('add')->check($param);
                } catch (ValidateException $e) {
                    // 验证失败 输出错误信息
                    return to_assign(1, $e->getError());
                }
                $param['souce'] = 1;
                $param['create_time'] = time();
                $insertId = Db::name('Keywords')->strict(false)->field(true)->insertGetId($param);
                if ($insertId) {
                    add_log('add', $insertId, $param);
                }

                return to_assign();
            }
        }
		else{
			$id = isset($param['id']) ? $param['id'] : 0;
			if ($id > 0) {
				$keywords = Db::name('Keywords')->where(['id' => $id])->find();
				View::assign('keywords', $keywords);
			}
			View::assign('id', $id);
			return view();
		}
    }

    //删除
    public function delete()
    {
        $id = get_params("id");
        $data['status'] = '-1';
        $data['id'] = $id;
        $data['update_time'] = time();
        if (Db::name('Keywords')->where("id",$id)->delete()) {
            add_log('delete', $id, $data);
            return to_assign(0, "删除成功");
        } else {
            return to_assign(1, "删除失败");
        }
    }


    /**
     * 批量删除
     * */
    function batch_delete(){
        $ids = get_params("ids");
        //print_r($ids);exit;
        if(!count($ids)){
            return to_assign(1, "未传入需要删除的数据");
        }
        if (Db::name('Keywords')->where("id",'in',$ids)->delete()) {
            clear_cache('keywords');
            add_log('delete', $ids);
            return to_assign(0, "删除成功");
        } else {
            return to_assign(1, "删除失败");
        }
    }


    /**
     * excel上传
     * */
    function batchupload(){
        if(request()->isAjax()){
//            $icon = get_params("icon");
//            $inputFileName = $_SERVER['DOCUMENT_ROOT'].$icon;
//            try {
//                $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
//                $objReader = PHPExcel_IOFactory::createReader($inputFileType);
//                $objPHPExcel = $objReader->load($inputFileName);
//            } catch(Exception $e) {
//                die('加载文件发生错误："'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
//            }
//            $sheet = $objPHPExcel->getSheet(0);
//            $data=$sheet->toArray();//该方法读取不到图片 图片需单独处理
//            $imageFilePath=$_SERVER['DOCUMENT_ROOT'].'/storage/'.date('Ymd').'/';//图片在本地存储的路径
//            if (! file_exists ( $imageFilePath )) {
//                mkdir("$imageFilePath", 0777, true);
//            }
////处理图片
//            foreach($sheet->getDrawingCollection() as $img) {
//                list($startColumn,$startRow)= PHPExcel_Cell::coordinateFromString($img->getCoordinates());//获取图片所在行和列
//                $imageFileName = $img->getCoordinates() . mt_rand(100, 999);
//
//                switch($img->getExtension()) {
//                    case 'jpg':
//                        $imageFileName.='.jpg';
//                        imagejpeg(imagecreatefromjpeg($img->getPath()),$imageFilePath.$imageFileName);
//                        break;
//                    case 'gif':
//                        $imageFileName.='.gif';
//                        imagegif(imagecreatefromgif($img->getPath()),$imageFilePath.$imageFileName);
//                        break;
//                    case 'png':
//                        $imageFileName.='.png';
//                        imagepng(imagecreatefrompng($img->getPath()),$imageFilePath.$imageFileName);
//                        break;
//                }
//                $startColumn = $this->ABC2decimal($startColumn);//由于图片所在位置的列号为字母，转化为数字
//                $data[$startRow-1][$startColumn]=$imageFilePath.$imageFileName;//把图片插入到数组中
//            }
//            exit;
            $icon = get_params("icon");
            $info = explode('.', $icon);
            $file_extension = $info[1];
            if ($file_extension == 'xlsx') {
                $objReader = IOFactory::createReader('Xlsx');
            } else {
                $objReader = IOFactory::createReader('Xls');
            }
            //$objReader = PHPExcel_IOFactory::createReader("Excel2007");
            $phpexcel = $objReader->load($_SERVER['DOCUMENT_ROOT'].$icon);
            //$phpexcel = PhpOfficePhpSpreadsheetIOFactory::load($_SERVER['DOCUMENT_ROOT'].$icon);
            $sheet = $phpexcel->getSheet(0);
            $highestRow = $sheet->getHighestRow();
            $highestColumn = $sheet->getHighestColumn();
            $data = [];
            $rows = [];
            for($row=2;$row<=$highestRow;$row++){
                $rowData = [];
                for($col='A';$col<=$highestColumn;$col++){
                    $rowData[$col] = (string)$sheet->getCell($col.$row);
                    $value = (string)$sheet->getCell($col.$row);
                    if($col=='A' && !empty($value) && !in_array($value,$data)){
                        $data[]=$value;
                    }
                }
                $rows[]=$rowData;
            }
           //echo "<pre>";
            //var_dump($highestRow,$highestColumn,$data,$rows);exit;
            if(model::where("title",'in',$data)->count()){
                return to_assign(1, "excel表格中有与数据库相同的数据");
            }
            $insert = [];
            foreach ($rows as $d){
                $insert[]=[
                    'title'=>$d['A'],
                    'herf'=>$d['B'],
                ];
            }
            Db::startTrans();
            try{
                model::insertAll($insert);
                Db::commit();
                return to_assign(0, "导入成功");
            }catch (ValidateException $e) {
                // 验证失败 输出错误信息
                Db::rollback();
                return to_assign(1, $e->getError());
            }
        }else{
            return view();
        }

    }

    function ABC2decimal($abc){
        $ten = 0;
        $len = strlen($abc);
        for($i=1;$i<=$len;$i++){
            $char = substr($abc,0-$i,1);//反向获取单个字符

            $int = ord($char);
            $ten += ($int-65)*pow(26,$i-1);
        }
        return $ten;
    }



}
