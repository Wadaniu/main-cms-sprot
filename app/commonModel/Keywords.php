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
use think\facade\Db;

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



    /**
     * 标签替换步骤
     * 1：先用标签库
     * 2：球队、联赛
     * */
    public function replaceLabel3A($article){

        //$keyword_names = [];

        $config = get_system_config('label');
        $amount = $config['amount'];
        if(intval($amount)<=0){
            return $article;
        }
        $replaceArr = [];

        //keywords标签库开始
        $keywords = self::where("status",1)->field("title,herf,id")->select();
        if($keywords){
            foreach ($keywords->toArray() as $kds){
                if($amount<=0){
                    return $article;
                }
                if(in_array($kds['title'],$replaceArr)){
                    continue;
                }

                //$pos = strpos($article['content'], $kds['title']);
                $pos = preg_match("/".$kds['title']."/",$article['content']);
                if ($pos) {
                    $replaceArr[] = $kds['title'];
                    $amount-=1;
                    //$article['content'] = substr_replace($article['content'],$kds['herf'],$pos,strlen($kds['title']));
                    $article['content'] = replace_keyword_outside_html($kds['title'],$kds['herf'],$article['content']);
                    $article['keyword_names'][] = [
                        'keyword'=>$kds['title'],
                        'replace'=>$kds['herf'],
                        'souce'=>2,
                    ];
                }
            }
        }
        //keywords标签库执行完成


        //联赛球队开始
        $footCate = (new ArticleCate())->getFootCate();
        if(in_array($article['cate_id'],$footCate)){
            $compId =  Db::name('comp_sort')
                ->where("type",0)
                ->order("sort desc")
                ->limit($config['rank'])
                ->column("comp_id");
            $competition = FootballCompetition::where("id", "in",$compId)
                ->field("id,name_zh,short_name_zh")
                ->select()
                ->toArray();
            $team = FootballTeam::where("competition_id","in",$compId)
                ->field("id,name_zh,short_name_zh")
                ->select()
                ->toArray();
        }else{
            $compId =  Db::name('comp_sort')
                ->where("type",1)
                ->order("sort desc")
                ->limit($config['rank'])
                ->column("comp_id");
            $competition = BasketballCompetition::where("id", "in",$compId)
                ->field("id,name_zh,short_name_zh")
                ->select()
                ->toArray();
            $team = BasketballTeam::where("competition_id","in",$compId)
                ->field("id,name_zh,short_name_zh")
                ->select()
                ->toArray();
        }
        foreach ($competition as $com){
            if($amount<=0){
                return $article;
            }
            if(in_array($com['short_name_zh'],$replaceArr) || $com['short_name_zh']==''){
                continue;
            }
            if($teamKeywords = Keywords::where("title",$com['short_name_zh'])->find()){
                continue;
            }
            $pos = preg_match("/".$com['short_name_zh']."/",$article['content']);
            if($pos){
                $replaceArr[] = $com['short_name_zh'];
                $amount-=1;
                $link = "/liansai-".(in_array($article['cate_id'],$footCate)?"zuqiu":"lanqiu")."/".$com['id'];
                $insert = [
                    'keyword'=>$com['short_name_zh'],
                    'replace'=>"<a href='".$link."'>".$com['short_name_zh']."</a>",
                    'souce'=>2,
                ];
                $article['content'] = replace_keyword_outside_html($com['short_name_zh'],$insert['replace'],$article['content']);
                $article['keyword_names'][] = $insert;
            }
        }

        //var_dump($compId);exit;

        foreach ($team as $com){
            if($amount<=0){
                return $article;
            }
            if(in_array($com['short_name_zh'],$replaceArr) || $com['short_name_zh']==''){
                continue;
            }
            if($teamKeywords = Keywords::where("title",$com['short_name_zh'])->find()){
                continue;
            }
            $pos = preg_match("/".$com['short_name_zh']."/",$article['content']);
            if($pos){
                $replaceArr[] = $com['short_name_zh'];
                $amount-=1;
                $link = "/qiudui-".(in_array($article['cate_id'],$footCate)?"zuqiu":"lanqiu")."/".$com['id'];
                $insert = [
                    'keyword'=>$com['short_name_zh'],
                    'replace'=>"<a href='".$link."'>".$com['short_name_zh']."</a>",
                    'souce'=>2,
                ];
                $article['content'] = replace_keyword_outside_html($com['short_name_zh'],$insert['replace'],$article['content']);
                $article['keyword_names'][] = $insert;
            }
        }


        //联赛球队结束
        return $article;
    }





}
