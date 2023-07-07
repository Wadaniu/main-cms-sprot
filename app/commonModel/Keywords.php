<?php
/**
 * @copyright Copyright (c) 2021 勾股工作室
 * @license https://opensource.org/licenses/Apache-2.0
 * @link https://www.gougucms.com
 */

namespace app\commonModel;

use Fukuball\Jieba\Finalseg;
use Fukuball\Jieba\Jieba;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\Model;

// 关键字模型
class Keywords extends Model
{
    const LabelCompTeamNo = 1;
    const LabelCompTeamOff = 2;


    // 关联关键字
    public function increase($keywords)
    {
        $is_exist = $this->where('title', $keywords['keyword'])->find();
        if ($is_exist) {
            $res = $is_exist['id'];
        } else {
            $res = $this->strict(false)->field(true)->insertGetId([
                    'title' => $keywords['keyword'],
                    'herf'=>$keywords['replace'],
                    'create_time' => time(),
                    'souce' => $keywords['souce']
                ]);
        }
        return $res;
    }

    /**
     * @throws ModelNotFoundException
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function getHot(){
        return self::where([
                    'status' => 1,
//                    'is_hot' => 1
                ])->order('create_time desc')->select();
    }

    public function replaceLabel2A($article)
    {
        // 初始化分词器
        Jieba::init();
        Finalseg::init();

        // 进行分词
        $seg_list = Jieba::cut($article['content']);

        //分词去重
        $seg_list = array_unique($seg_list);

        //过滤符号
        $pattern = '/([^\w\s]|_)(?!\S)/u';
        foreach ($seg_list as $key => $value) {
            // 判断值是否匹配正则表达式模式
            if (preg_match($pattern, $value)) {
                // 删除匹配的数据
                unset($seg_list[$key]);
            }
        }

        //获取标签配置
        $config = get_system_config('label');

        //获取用户自定义标签
        $labels = self::where('status',1)->column('herf','title');

        $replaceArr = [];

        //匹配用户自定义标签
        foreach ($seg_list as $key => $seg){
            if (count($replaceArr) >= $config['amount']){
                break;
            }
            if (isset($labels[$seg])){
                $replaceArr[] = [
                    'keyword'   =>  $seg,
                    'replace'   =>   "<a href='$labels[$seg]' title='$seg' >$seg</a>"
                ];
                unset($seg_list[$key]);
            }
        }

        //根据配置获取关键字匹配对象
        if (count($replaceArr) < $config['amount'] && $config['liansaiteam'] == self::LabelCompTeamNo){
            //判断篮球OR足球
            $articleCate = new ArticleCate();
            if ($articleCate->judgeCateById($article['cate_id'])){
                //获取篮球联赛和球队
                $comp = new BasketballCompetition();
                $team = new BasketballTeam();
                $compLinkPre = '/liansai-lanqiu/';
                $teamLinkPre = '/qiudui-lanqiu/';
            }else{
                $comp = new FootballCompetition();
                $team = new FootballTeam();
                $compLinkPre = '/liansai-zuqiu/';
                $teamLinkPre = '/qiudui-zuqiu/';
            }

            foreach ($seg_list as $key => $seg){
                $link = '';
                if (count($replaceArr) >= $config['amount']){
                    break;
                }
                $compInfo = $comp->getCacheByName($seg);
                if (!$compInfo === false){
                    $link = $compLinkPre.$compInfo['id'];
                    unset($seg_list[$key]);
                }
                $teamInfo = $team->getTeamCacheByName($seg);
                if (!$teamInfo === false){
                    $link = $teamLinkPre.$teamInfo['id'];
                    unset($seg_list[$key]);
                }

                if (!empty($link)){
                    $replaceArr[] = [
                        'keyword'   =>  $seg,
                        'replace'   =>  "<a href='$link' title='$seg' >$seg</a>",
                        'souce'     =>  2
                    ];
                }
            }
        }
        //取替换列表替换文章关键字
        foreach ($replaceArr as $replace){
            $article['content'] = replace_keyword_outside_html($replace['keyword'],$replace['replace'],$article['content']);
        }

        $article['keyword_names'] = $replaceArr;
        return $article;
    }
}
