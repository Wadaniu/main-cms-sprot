<?php

namespace app\crud\make\make;

use app\crud\make\ToAutoMake;
use think\facade\App;
use think\facade\Db;
use think\console\Output;

class EditMake implements ToAutoMake
{
    public function check($table, $path)
    {
        !defined('DS') && define('DS', DIRECTORY_SEPARATOR);

        $modelName = $table;
        $modelFilePath = base_path() . $path . DS . 'view' . DS . $modelName . DS . 'edit.html';

        if (!is_dir(base_path() . $path . DS . 'view' . DS . $modelName)) {
            mkdir(base_path() . $path . DS . 'view'. DS . $modelName, 0755, true);
        }

        if (file_exists($modelFilePath)) {
            $output = new Output();
            $output->error("$modelName . DS . edit.html已经存在");
            exit;
        }
    }

    public function make($table, $path, $other)
    {
        $editTpl = dirname(dirname(__DIR__)) . '/tpl/edit.tpl';
        $tplContent = file_get_contents($editTpl);

        $model = $table;
        $filePath = empty($path) ? '' : DS . $path;
        $namespace = empty($path) ? '\\' : '\\' . $path . '\\';
		
		$prefix = config('database.connections.mysql.prefix');
        $column = Db::query('SHOW FULL COLUMNS FROM `' . $prefix . $table . '`');
        $pk = '';
        foreach ($column as $vo) {
            if ($vo['Key'] == 'PRI') {
                $pk = $vo['Field'];
                break;
            }
        }
		/*
		//读取数据结构生成字段
		$tritems ='';
		$detail ='$detail.';
		$index =0;
        foreach ($column as $key => $vo) {
			$field = $vo['Field'];
			$title = $vo['Comment']==''?$field:$vo['Comment'];
		if($field != 'id'){
		if(($index % 3) == 0){
			$tritems.="<tr>
			<td class='layui-td-gray-2'>{$title}</td>
			<td><input type='text' name='{$field}' value='{{$detail}{$field}}' placeholder='请输入{$title}' class='layui-input' autocomplete='off' /></td>"; 
		}else if(($index % 3) == 1){
				$tritems.="
			<td class='layui-td-gray-2'>{$title}</td>
			<td><input type='text' name='{$field}' value='{{$detail}{$field}}' placeholder='请输入{$title}' class='layui-input' autocomplete='off' /></td>";
		}else if(($index % 3) == 2){
				$tritems.="
			<td class='layui-td-gray-2'>{$title}</td>
			<td><input type='text' name='{$field}' value='{{$detail}{$field}}' placeholder='请输入{$title}' class='layui-input' autocomplete='off' /></td>
		</tr>
		";
		}
		$index++;
			}      
        }
		if(($index % 3) == 1){
			$tritems.="<td colspan='4'></td>
		</tr>";
		}
		if(($index % 3) == 2){
			$tritems.="<td colspan='2'></td>
		</tr>";
		}
		*/
		
		//读取提交的数据生成字段
        $field_column = get_cache('crud_e_'.$table);
		$tritems ='';
		$index =0;
		$summernoteIndex=0;
		$inputHtml='';
		$textareaHtml='';
		$uploadHtml='';
		$uploadScript = '';
		$summernoteHtml='';
		$summernoteForm='';
		$summernoteScript='';
		$datetimeScript='';
        foreach ($field_column as $key => $vo) {
			$field = $vo['field'];
			$title = $vo['title'];
			if($vo['type'] == 'summernote'){
				$summernoteHtml.="<tr>".$this->make_form($vo['field'], $vo['type'], $vo['title'],$vo['required'])."
		</tr>";
		
			$summernoteScript.= "//".$vo['title']."富文本编辑器
		var edit = layui.tinymce.render({
			selector: '#container_".$vo['field']."',
			height: 500
		});";
		
			$summernoteForm.= "data.field.".$vo['field']." = tinyMCE.editors['container_".$vo['field']."'].getContent();
			if (data.field.".$vo['field']." == '') {
				layer.msg('请先完善".$vo['title']."内容');
				return false;
			}";
			$summernoteIndex++;
			}
			else if($vo['type'] == 'upload'){			
				$uploadHtml.="<tr>".$this->make_form($vo['field'], $vo['type'], $vo['title'],$vo['required'])."
		</tr>";
		$uploadScript.= "//上传".$vo['title']."
		var upload_".$vo['field']." = layui.upload.render({
			elem: '#upload_btn_".$vo['field']."',
			url: '/admin/api/upload',
			done: function (res) {
				//如果上传失败
				if (res.code == 1) {
					return layer.msg('上传失败');
				}
				//上传成功
				$('#upload_box_".$vo['field']." input').attr('value', res.data.filepath);
				$('#upload_box_".$vo['field']." img').attr('src', res.data.filepath);
			}
		});";
			}else if($vo['type'] == 'textarea'){			
				$textareaHtml.="<tr>".$this->make_form($vo['field'], $vo['type'], $vo['title'],$vo['required'])."
		</tr>";
			}else if($vo['type'] == 'textarea'){			
				$textareaHtml.="<tr>".$this->make_form($vo['field'], $vo['type'], $vo['title'],$vo['required'])."
		</tr>";
			}
			else{
				if(($index % 3) == 0){
					$inputHtml.="<tr>".$this->make_form($vo['field'], $vo['type'], $vo['title'],$vo['required']);
				}else if(($index % 3) == 1){
						$inputHtml.=$this->make_form($vo['field'], $vo['type'], $vo['title'],$vo['required']);
				}else if(($index % 3) == 2){
						$inputHtml.=$this->make_form($vo['field'], $vo['type'], $vo['title'],$vo['required'])."
		</tr>
				";
				}
				if($vo['type'] == 'datetime'){		
		$datetimeScript.="//日期选择
		layui.laydate.render({
			elem: '#laydate_".$vo['field']."' //指定元素
		});";
				}
				$index++;    
			}
		}
		if(($index % 3) == 1){
			$inputHtml.="<td colspan='4'></td>
		</tr>";
		}
		if(($index % 3) == 2){
			$inputHtml.="<td colspan='2'></td>
		</tr>";
		}
		
		$moduleInit = "var moduleInit = ['tool'];";
		if($summernoteIndex>0){
			$moduleInit = "var moduleInit = ['tool','tinymce'];";
		}
		
		$tritems=$inputHtml.$textareaHtml.$uploadHtml.$summernoteHtml;
        $tplContent = str_replace('<namespace>', $namespace, $tplContent);
        $tplContent = str_replace('<model>', $model, $tplContent);
        $tplContent = str_replace('<tritems>', $tritems, $tplContent);
        $tplContent = str_replace('<name>', $other, $tplContent);
		$tplContent = str_replace('<pk>', $pk, $tplContent);
        $tplContent = str_replace('<moduleInit>', $moduleInit, $tplContent);
		$tplContent = str_replace('<datetimeScript>', $datetimeScript, $tplContent);
        $tplContent = str_replace('<uploadScript>', $uploadScript, $tplContent);
        $tplContent = str_replace('<summernoteForm>', $summernoteForm, $tplContent);
        $tplContent = str_replace('<summernoteScript>', $summernoteScript, $tplContent);

        file_put_contents(base_path() . $path . DS . 'view' . DS . $model . DS . 'edit.html', $tplContent);
    }
	
	public function make_form($field, $type, $title,$required)
    {
		$required_font = '';
		$required_verify = '';
		if($required==1){
			$required_font = '<font>*</font>';
			$required_verify = ' lay-verify="required" lay-reqText="请完善'.$title.'"';
		}
		$tem=[
			'input'=>'<td class="layui-td-gray-2">'.$title.$required_font.'</td>
			<td><input type="text" name="'.$field.'" '.$required_verify.' value="{$detail.'.$field.'}" autocomplete="off" placeholder="请输入'.$title.'" class="layui-input"></td>',
			
			'datetime'=>'<td class="layui-td-gray-2">'.$title.$required_font.'</td>
			<td><input type="text" name="'.$field.'" '.$required_verify.' value="{$detail.'.$field.'|time_format=###,\'Y-m-d\'}" readonly readonly id="laydate_'.$field.'" autocomplete="off" placeholder="请选择" class="layui-input"></td>',
			
			'radio'=>'<td class="layui-td-gray-2">'.$title.$required_font.'</td>
			<td>
				<input type="radio" name="'.$field.'" value="0" title="选项一" {eq name="$detail.'.$field.'" value="1"} checked{/eq}>
				<input type="radio" name="'.$field.'" value="1" title="选项二" {eq name="$detail.'.$field.'" value="2"} checked{/eq}>
			</td>',
			
			'checkbox'=>'<td class="layui-td-gray-2">'.$title.$required_font.'</td>
			<td>
				<input type="checkbox" name="'.$field.'" value="1" title="选项一" lay-skin="primary" {eq name="$detail.'.$field.'" value="1"} selected{/eq}>
				<input type="checkbox" name="'.$field.'" value="2" title="选项二" lay-skin="primary" {eq name="$detail.'.$field.'" value="2"} selected{/eq}>
			</td>',
			
			'select'=>'<td class="layui-td-gray-2">'.$title.$required_font.'</td>
			<td>
				<select name="'.$field.'" '.$required_verify.'>
					<option value="">请选择</option>
					<option value="1" {eq name="$detail.'.$field.'" value="1"} selected{/eq}>选项一</option>
					<option value="2" {eq name="$detail.'.$field.'" value="2"} selected{/eq}>选项二</option>
				</select>
			</td>',
			
						
			'textarea'=>'<td class="layui-td-gray-2">'.$title.$required_font.'</td>
			<td colspan="5"><textarea name="'.$field.'" '.$required_verify.' placeholder="请输入'.$title.'" class="layui-textarea">{$detail.'.$field.'}</textarea></td>',
			
			'upload'=>'<td class="layui-td-gray-2">'.$title.$required_font.'</td>
			<td colspan="5" style="vertical-align:top">
				<div class="layui-upload">
					<button type="button" class="layui-btn layui-btn-sm" id="upload_btn_'.$field.'">选择上传图片</button>
					<div class="layui-upload-list" id="upload_box_'.$field.'">
						<img src="{$detail.'.$field.'}" onerror="javascript:this.src=\'{__GOUGU__}/gougu/images/nonepic600x360.jpg\';this.onerror=null;" style="width:200px;max-width:200px" />
						<input type="hidden" name="'.$field.'" value="{$detail.'.$field.'}" '.$required_verify.'>
					</div>
				</div>
			</td>',
			
			'summernote'=>'<td class="layui-td-gray-2">'.$title.$required_font.'</td>
			<td colspan="5">
				<textarea class="layui-textarea" id="container_'.$field.'">{$detail.'.$field.'}</textarea>
			</td>'
		];
		return $tem[$type];
	}
}