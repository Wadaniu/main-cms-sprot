<?php
/**
 * @copyright Copyright (c) 2021 勾股工作室
 * @license https://opensource.org/licenses/Apache-2.0
 * @link https://www.gougucms.com
 */

declare (strict_types = 1);

namespace app\home\controller;

use app\home\BaseController;
use think\facade\Cache;
use think\facade\Env;
use think\facade\View;

class Index extends BaseController
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::initialize();
    }


    public function index(): \think\response\View
    {
        $param = get_params();
        $id = $param['id'] ?? 0;

        $redisKey = 'footballIndexData'.$id;
        $data = Cache::get($redisKey);

        if ($data) {
            $data = json_decode($data,true);
        }else{
            $data['hotCompetition'] = getHotComp();

            //获取首页赛程
            $model = new \app\commonModel\FootballMatch();
            if (empty($id)){
                $id = Env::get('Home.HOME_SPACE');
                $compIds = array_column($data['hotCompetition'],'id');
                //id为空取热门数据
                $match = $model->getWeekData($compIds);
            }else{
                $match = $model->getFootballMatchIndex($id);
            }

            $data['match'] = [];

            //排序,优先比赛中,其次已完赛,最后未开赛
            foreach ($match as $key => $item){
                if (in_array($item['status_id'],[2,3,4,5,7])){
                    $data['match'][] = $item;
                    unset($match[$key]);
                }
            }
            foreach ($match as $key => $item){
                if ($item['status_id'] == 8){
                    $data['match'][] = $item;
                    unset($match[$key]);
                }
            }
            foreach ($match as $key => $item){
                if ($item['status_id'] == 1){
                    $data['match'][] = $item;
                    unset($match[$key]);
                }
            }

            //联赛信息,默认env配置联赛
            $footballCompetition = new  \app\commonModel\FootballCompetition();
            $data['compInfo'] = $footballCompetition->info($id);

            //缓存
            Cache::set($redisKey, json_encode($data),180);
        }

        $labArr = array_column($data['hotCompetition'],'short_name_zh','id');
        if (array_key_exists($id,$labArr)){
            $lab = $labArr[$id];
            //【联赛名】直播,免费观看【联赛名】比赛,【联赛名】直播免费观看_【联赛名】直播比赛 - 24直播网
            $data['seo']['title'] = $lab.'直播,免费观看'.$lab.'比赛,'.$lab.'直播免费观看_'.$lab.'直播比赛 - '.$this->webCommonTitle;
            //【联赛】直播,【联赛】直播免费观看,【联赛】高清无插件,24直播网
            $data['seo']['keywords'] = $lab.'直播,'.$lab.'直播免费观看,'.$lab.'高清无插件,'.$this->webCommonTitle;
            //24直播网专业提供【联赛】直播,【联赛】直播免费观看,【联赛】直播免费高清在线直播,【联赛】直播在线观看直播,【联赛】直播高清直播观看,【联赛】直播比赛,【联赛】直播高清无插件 - 24直播网
            $data['seo']['description'] = $this->webCommonTitle.'专业提供'.$lab.'直播,'.$lab.'直播免费观看,'.$lab.'直播免费高清在线直播,'.
                $lab.'直播在线观看直播,'.$lab.'直播高清直播观看,'.$lab.'直播比赛,'.$lab.'直播高清无插件 - '.$this->webCommonTitle;
            View::assign("seo",$data['seo']);
        }

        View::assign("match",$data['match']);
        View::assign("compInfo",$data['compInfo']);
        View::assign("id",$id);
        return View('');
    }

}
