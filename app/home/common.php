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

function get_navs_es($name)
{
    if (!get_cache('homeNavEs' . $name)) {
        $nav_id = \think\facade\Db::name('Nav')->where(['name' => $name, 'status' => 1])->value('id');
        if (empty($nav_id)) {
            return '';
        }
        $nav = \think\facade\Db::name('NavInfo')->where('nav_id', $nav_id)->order('sort asc')->select()->toArray();
        \think\facade\Cache::set('homeNavEs' . $name, $nav);
    }
    $navs = get_cache('homeNavEs' . $name);
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
    $model = new \app\commonModel\FootballMatch();
    //联赛历史七天赛程
    $matchBack = $model->getWeekHistoryData([$id], 7);
    return $matchBack;
}

function getNewsShort()
{
    $id = Env::get('Home.HOME_SPACE');
    //首页文章
    $articleModel = new \app\commonModel\Article();
    $where = 'a.competition_id = ' . $id;
    $articleList = $articleModel->getListByCompId($where, ['limit' => 6]);

    return $articleList;
}

function getHotKeywords()
{
    //获取热门标签
    $keywordModel = new \app\commonModel\Keywords();
    $labels = $keywordModel->getHot();
    if (!$labels) {
        $labels = [];
    }
    return $labels;
}

function getFootballHotComp(){
    $Competition = new  \app\commonModel\FootballCompetition();
    return $Competition->getHotData();
}

function getBasketballHotComp(){
    $Competition = new  \app\commonModel\BasketballCompetition();
    return $Competition->getHotData();
}

function getMainMatchLive(){
    $footballCompetition = new  \app\commonModel\MatchliveLink();
    return $footballCompetition->getList();
}

function getHomeRule()
{
    $route = \think\facade\Route::getRuleList();
    $level = [];
    foreach ($route as $item){
        if ($item['name'] == '/'){
            $level[$item['name']] = [];
            continue;
        }
        $routes = explode('/',$item['name']);
        if (array_key_exists($routes[0],$level)){
            $level[$routes[0]]['child'][] = $item['name'];
        }else{
            $level[$routes[0]]['child'] = [];
        }
    }
    return $level;
}

function getFatherRule()
{
    $route = \think\facade\Route::getRuleList();
    $level = [];
    foreach ($route as $item){
        if ($item['name'] == '/'){
            $level[] = $item['name'];
            continue;
        }
        $routes = explode('/',$item['name']);
        if (array_key_exists($routes[0],$level)){
            continue;
        }else{
            $level[]= $routes[0];
        }
    }
    return $level;
}