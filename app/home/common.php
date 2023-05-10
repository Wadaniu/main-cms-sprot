<?php

use think\facade\Env;

/**
 * @copyright Copyright (c) 2021 勾股工作室
 * @license https://opensource.org/licenses/Apache-2.0
 * @link https://www.gougucms.com
 */

// 这是home公共文件

//读取导航列表，用于前台
function get_navs($name)
{
    if (!get_cache('homeNav' . $name)) {
        $nav_id = \think\facade\Db::name('Nav')->where(['name' => $name, 'status' => 1])->value('id');
        if (empty($nav_id)) {
            return '';
        }
        $list = \think\facade\Db::name('NavInfo')->where(['nav_id' => $nav_id, 'status' => 1])->order('sort asc')->select()->toArray();
        $nav = list_to_tree($list);
        \think\facade\Cache::tag('homeNav')->set('homeNav' . $name, $nav);
    }
    $navs = get_cache('homeNav' . $name);

    return $navs;
}

//读取指定文章的详情
function get_article_detail($id)
{
    $article = \think\facade\Db::name('article')->where(['id' => $id])->find();
    if (empty($article)) {
        return false;
    }
    $keywrod_array = \think\facade\Db::name('ArticleKeywords')
        ->field('i.aid,i.keywords_id,k.title')
        ->alias('i')
        ->join('keywords k', 'k.id = i.keywords_id', 'LEFT')
        ->order('i.create_time asc')
        ->where(array('i.aid' => $id, 'k.status' => 1))
        ->select()->toArray();

    $article['keyword_ids'] = implode(",", array_column($keywrod_array, 'keywords_id'));
    $article['keyword_names'] = implode(',', array_column($keywrod_array, 'title'));
    return $article;
}


function getLink()
{
    $model = \think\facade\Db::name('links')->cache(true, 600)->where(['status' => 1])->select();
    return $model;
}

/**
 * @return array
 * @throws \think\db\exception\DataNotFoundException
 * @throws \think\db\exception\DbException
 * @throws \think\db\exception\ModelNotFoundException
 * 获取上一篇
 */
function articlePrev($id, $cateId = 0)
{
    $map = array();
    if (!empty($cateId)) {
        $map[] = ["cate_id", '=', $cateId];
    }
    $map[] = ["id", '<', $id];
    $article = \think\facade\Db::name('article')
        ->where($map)
        ->field("id,competition_id,title")
        ->order("id desc")
        ->cache(true, 300)
        ->find();
    return $article;
}

function articleNext($id, $cateId = 0)
{
    $map = array();
    if (!empty($cateId)) {
        $map[] = ["cate_id", '=', $cateId];
    }
    $map[] = ["id", '>', $id];
    $article = \think\facade\Db::name('article')
        ->where($map)
        ->field("id,competition_id,title")
        ->order("id asc")
        ->cache(true, 300)
        ->find();
    return $article;
}

function toArticle($cateId, $limit = 6)
{
    $map = array();
    if (!empty($cateId)) {
        if (is_array($cateId)) {
            $map[] = ["cate_id", 'in', $cateId];
        } else {
            $map[] = ["cate_id", '=', $cateId];
        }
    }
    $map[] = ["thumb", '>', 0];

    $article = \think\facade\Db::name('article')
        ->where($map)
        ->field("id,cate_id,desc,thumb,competition_id,title,update_time")
        ->order("id asc")
        ->limit($limit)
        ->cache(true, 300)
        ->select()
        ->toArray();
    return $article;
}

function sphereText($type = "")
{
    if (empty($type)) {
        $type = $_GET['type'];
    }
    if ($type == "football") {
        return "足球";
    }
    return "蓝球";
}

function competitionText($competitionId)
{
    $basketballCompetition = new  \app\admin\model\BasketballCompetition();
    $basketballHotData = $basketballCompetition->getHotData();
    $football = new  \app\admin\model\FootballCompetition();
    $footballHotData = $football->getHotData();
    $data = array_merge($basketballHotData, $footballHotData);
    if (isset($data[$competitionId])) {
        $zh = $data[$competitionId]["short_name_zh"];
        if (empty($zh)) {
            $zh = $data[$competitionId]["name_zh"];;
        }
        return $zh;
    }

    return "";
}

/*function getHotCompetition(){
    $data = array();
    $basketballCompetition = new  \app\admin\model\BasketballCompetition();
    $basketballHotData = $basketballCompetition->getHotData();
    $data["basketball"]=$basketballHotData;
    $football = new  \app\admin\model\FootballCompetition();
    $footballHotData = $football->getHotData();
    $data["football"]=$footballHotData;
    return $data;
}*/


function hotLive()
{
    $basketballCompetition = new  \app\admin\model\BasketballCompetition();
    $basketballHotData = $basketballCompetition->getHotData();
    $footballCompetition = new  \app\admin\model\FootballCompetition();
    $footballHotData = $footballCompetition->getHotData();
    $basketballIds = array();
    $footballIds = array();
    foreach ($basketballHotData as $vo) {
        $basketballIds[] = $vo["id"];
    }
    foreach ($footballHotData as $vo) {
        $footballIds[] = $vo["id"];
    }
    $basketballMatch = new \app\admin\model\BasketballMatch();
    $footballMatch = new \app\admin\model\FootballMatch();
    $startTime = strtotime(date('Y-m-d', strtotime('-2 days')));
    $endTime = strtotime(date("Y-m-d", strtotime("+1 days"))) - 1;
    $where[] = ['match_time', 'between', [$startTime, $endTime]];
    $basketball = $basketballMatch->getMatchInfo($where, $basketballIds, 6, "match_time desc");
    $football = $footballMatch->getMatchInfo($where, $footballIds, 6, "match_time desc");
    $voList = array_merge($basketball, $football);
    $data = [];
    foreach ($voList as $key => $vo) {
        if ($key < 6) {
            $data[] = $vo;
        } else {
            break;
        }

    }
    return $data;
}

function scoresCount($scores)
{
    $data = json_decode($scores, true);
    $count = 0;
    foreach ($data as $val) {
        $count = $count + intval($val);
    }
    return $count;
}

function getzt($id, $type): string
{
    $back = '';
    $zt = [0, 1, 9, 10, 11, 12, 13];
    $boolean = in_array($id, $zt);

    switch ($type) {
        case 0:
            $back = $boolean ? '' : 'ing';
            break;
        case 1:
            $back = $boolean ? '赛前分析' : ($id == 8 ? '锦集/录像' : '直播中...');
            break;
        case 2:
            $back = $boolean ? 'analyse' : 'play';
            break;
    }

    return $back;
}

function getstyle($num1, $num2): string
{
    $num = config('view')['view_dir_name'] == 'view' ? 78 : 65;
    $total = $num1 + $num2;
    if ($total > 0) {
        $width = $num1 / $total * $num;
    } else {
        $width = 0;
    }
    $win = ($num1 - $num2) > 0 ? 'class=win' : '';
    return "style=width:$width% $win";
}

function getHistoryMatch(): array
{
    $id = Env::get('Home.HOME_SPACE');
    $model = new \app\admin\model\FootballMatch();
    //联赛历史七天赛程
    $matchBack = $model->getWeekHistoryData([$id], 7);
    return $matchBack;
}

function getNewsShort()
{
    $id = Env::get('Home.HOME_SPACE');
    //首页文章
    $articleModel = new \app\admin\model\Article();
    $where = 'a.competition_id = ' . $id;
    $articleList = $articleModel->getListByCompId($where, ['limit' => 6]);

    return $articleList;
}

function getHotKeywords()
{
    //获取热门标签
    $keywordModel = new \app\admin\model\Keywords();
    $labels = $keywordModel->getHot();
    if (!$labels) {
        $labels = [];
    }
    return $labels;
}

function getHotComp(){
    $footballCompetition = new  \app\admin\model\FootballCompetition();
    return $footballCompetition->getHotData();
}

function getMainMatchLive(){
    $footballCompetition = new  \app\admin\model\MatchliveLink();
    return $footballCompetition->getList();
}