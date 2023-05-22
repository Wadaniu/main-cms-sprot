<?php
/**
 * @copyright Copyright (c) 2021 勾股工作室
 * @license https://opensource.org/licenses/Apache-2.0
 * @link https://www.gougucms.com
 */

declare (strict_types = 1);

namespace app\home;

use think\App;
use think\exception\HttpResponseException;
use think\facade\Request;
use think\facade\View;

/**
 * 控制器基础类
 */
abstract class BaseController
{
    /**
     * Request实例
     * @var \think\Request
     */
    protected $request;

    /**
     * 应用实例
     * @var \think\App
     */
    protected $app;

    /**
     * 是否批量验证
     * @var bool
     */
    protected $batchValidate = false;

    /**
     * 控制器中间件
     * @var array
     */
    protected $middleware = [];

    /**
     * webtitle
     * @var string
     */
    protected $webTitle = '';
    /**
     * 网站名称
     * @var string
     */
    protected $web_common_title = '';
    /**
     * 系统名称
     * @var string
     */
    protected $webAdminTitle = '';

    protected $webKeywords = '';
    protected $webDesc = '';

    protected $nav = [];
    /**
     * 构造方法
     * @access public
     * @param  App  $app  应用对象
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->request = $this->app->request;
        // 控制器初始化
        $this->initialize();
//        $this->autoSync();
    }

    // 初始化
    protected function initialize()
    {
        $this->nav = array_column(get_navs_es('NAV_HOME'),null,'route_tag');
        $COMMON_NAV = get_navs('NAV_HOME');
        $seo = Request::rule();
        //动态渲染title
        $this->web_common_title = get_system_config('web','title');
        $this->webAdminTitle = get_system_config('web','admin_title');
        foreach ($COMMON_NAV as $item){
            if ($item['src'] == $seo->getName() ){
                $this->webTitle = $item['web_title'];
                $this->webKeywords = $item['web_keywords'];
                $this->webDesc = $item['web_desc'];
                break;
            }
        }

        $this->webTitle = $this->webTitle ?? $this->webAdminTitle;

        $seo = [
            'title' => $this->webTitle,
            'keywords' => $this->webKeywords,
            'description' => $this->webDesc,
        ];

        View::assign('web_name',$this->web_common_title);
        View::assign('COMMON_NAV', $COMMON_NAV);
        View::assign('seo', $seo);
        View::assign('webconfig', get_config('webconfig'));

    }

	//页面跳转方法
	public function redirectTo(...$args)
    {
        throw new HttpResponseException(redirect(...$args));
    }

    /**
     * 替换tkd模板
     * @param $tempStr
     * @param $tdkObj
     * @return array|mixed|string|string[]
     */
    protected function replaceTDK($tempStr,$tdkObj){
        preg_match_all('/(\[).*?(\])/', $tempStr, $matches);

        foreach ($matches[0] as $field){
            $temp = trim($field,'[]');

            $replace = $tdkObj->$temp ?? '';

            //用#分割判断是否为日期
            $tempArr = explode('#',$temp);
            if (count($tempArr) > 1){
                if (in_array('date',$tempArr)){
                    $replace = date($tempArr[1],time());
                }else{
                    $replace = date($tempArr[1],$tdkObj->$temp);
                }
            }
            //判断是否为网站名称
            if ($temp == 'web_common_title'){
                $replace = $this->web_common_title;
            }
            $tempStr = str_replace($field,$replace,$tempStr);
        }

        return $tempStr;
    }


}
