<?php

use think\facade\Env;
use think\facade\Request;
use think\facade\Cache;
use think\facade\Db;

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
    $map[] = ['delete_time', '=', 0];
    $article = \think\facade\Db::name('article')
        ->where($map)
        ->field("id,competition_id,title,cate_id")
        ->order("id desc")
        //->cache(true, 300)
        ->find();
    if (!$article) {
        return [];
    }

    $footCate = (new \app\commonModel\ArticleCate())->getFootCate();
    if (in_array($article['cate_id'], $footCate)) {
        $competition = (new \app\commonModel\FootballCompetition())->getShortNameZh($article['competition_id']);
        $article['cate_id'] = 1;
    } else {
        $competition = (new \app\commonModel\BasketballCompetition())->getShortNameZh($article['competition_id']);
        $article['cate_id'] = 2;
    }
    if (!$competition) {
        $article['short_name_py'] = in_array($article['cate_id'], $footCate) ? 'zuqiu' : 'lanqiu';
    } else {
        $article['short_name_py'] = $competition['short_name_py'];
    }
    return $article;
}

function articleNext($id, $cateId = 0)
{
    $map = array();
    if (!empty($cateId)) {
        $map[] = ["cate_id", '=', $cateId];
    }
    $map[] = ["id", '>', $id];
    $map[] = ['delete_time', '=', 0];
    $article = \think\facade\Db::name('article')
        ->where($map)
        ->field("id,competition_id,title,cate_id")
        ->order("id asc")
        //->cache(true, 300)
        ->find();
    if (!$article) {
        return [];
    }
    $footCate = (new \app\commonModel\ArticleCate())->getFootCate();
    if (in_array($article['cate_id'], $footCate)) {
        $competition = (new \app\commonModel\FootballCompetition())->getShortNameZh($article['competition_id']);
        $article['cate_id'] = 1;
    } else {
        $competition = (new \app\commonModel\BasketballCompetition())->getShortNameZh($article['competition_id']);
        $article['cate_id'] = 2;
    }
    if (!$competition) {
        $article['short_name_py'] = in_array($article['cate_id'], $footCate) ? 'zuqiu' : 'lanqiu';
    } else {
        $article['short_name_py'] = $competition['short_name_py'];
    }

    return $article;
}

function getzt($id, $type, $iszq): string
{
    $back = '';
    $zt = $iszq == 'zuqiu' ? [0, 1, 9, 10, 11, 12, 13] : [0, 1, 11, 12, 13, 14, 15];
    $boolean = in_array($id, $zt);

    switch ($type) {
        case 0:
            $back = $boolean ? 'icon-fenxi' : ($id == ($iszq == 'zuqiu' ? 8 : 10) ? 'icon-bofang' : 'icon-zhibo');
            break;
        case 1:
            $back = $boolean ? '赛前分析' : ($id == ($iszq == 'zuqiu' ? 8 : 10) ? '锦集/录像' : '直播中...');
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

function getScore($fraction)
{
    $total = 0;
    foreach ($fraction as $num) {
        $total += $num;
    }
    return $total;
}

function strtoarr($name, $str, $logo, $text)
{
    $split1 = explode("^", $str);
    $split2 = [];
    $split3 = ['team_name_logo' => $logo, 'team_name_text' => $text];
    foreach ($split1 as $i => $item) {
        if ($i != 0 && $i != 12) {
            $split2 = array_merge($split2, explode("-", $item));
        }
    }
    foreach ($name as $i => $item) {
        $split3[$item] = $split2[$i];
    }
    return $split3;
}

//整理篮球技术统计
function getteamStats($name, $home_team, $away_team, $info)
{
    return [strtoarr($name, $home_team, $info['home_team_logo'], $info['home_team_text']), strtoarr($name, $away_team, $info['away_team_logo'], $info['away_team_text'])];
}

//整理篮球球员统计
function getplaydata($data)
{
    $playdata = [];
    $index = [0, 1, 2, 3, 6, 7, 8, 9, 10, 11, 13];
    foreach (explode("^", $data) as $i => $item) {
        if (in_array($i, $index)) {
            $playdata[] = $item;
        }
    }
    return $playdata;
}

function moresrc($name, $bool = true)
{
    $compname = get_params('compname');
    $subdivision = $bool ? ($compname && !strpos($compname, '_') ? $compname : '') : '';
    return '/' . $name . '-' . (strpos(get_ruleName(), 'zuqiu') ? 'zuqiu/' : 'lanqiu/') . $subdivision;
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

function typename(): string
{
    return strpos($_SERVER['REQUEST_URI'], 'zuqiu') ? '足球' : (strpos($_SERVER['REQUEST_URI'], 'lanqiu') ? '篮球' : '');
}

//某字符在字符串中出现某次的下标
function findIndex($str, $target, $num): int
{
    $index = 0;
    $count = 0;
    for ($i = 0; $i < strlen($str); $i++) {
        if ($str[$i] == $target) {
            $index = $i;
            $count++;
        }
        if ($count == $num) {
            break;
        }
    }
    return $index;
}

//热门分类选择
function typeselect(): array
{
    $cururl = $_SERVER['REQUEST_URI'];//获取当前浏览器patch地址
    $alllink = count(get_params()) ? substr($cururl, 0, findIndex($cururl, '/', 2) + 1) : $cururl;//截取patch中的主路径
    if ($alllink == '/zixun/') return [];//特殊情况处理
    $typelist[] = ['title' => '全部', 'py' => '/all', 'src' => $alllink];//默认全部分类信息
    $typedata = strpos($alllink, 'zuqiu') ? getFootballHotComp() : getBasketballHotComp();//根据类别获取不同分类
    foreach ($typedata as $item) {
        $typelist[] = [
            'title' => $item['short_name_zh'],
            'py' => '/' . $item['short_name_py'],
            'src' => (substr($alllink, -1) == '/' ? $alllink : $alllink . '/') . $item['short_name_py']
        ];
    }
    return $typelist;
}

//全部热门类别
function hotlive($src, $name = ''): array
{
    $typelist = [];

    $hottype = array_merge(getFootballHotComp(), getBasketballHotComp());
    //排序
    array_multisort(array_column($hottype, 'sort'), SORT_DESC, $hottype);
    foreach ($hottype as $type) {
        $typelist[] = [
            'id' => $type['id'],
            'type' => $type['sphere_type'],
            'title' => $type['short_name_zh'] . $name,
            'src' => '/' . $src . '-' . $type['sphere_type'] . '/' . $type['short_name_py']
        ];
    }

    return $typelist;
}

//获取移动端导航链接
function wapnav($list)
{
    $link = '';
    foreach ($list as $t) {
        $link = $t['src'] . $t['param'];
        if ($link != '') {
            return $link;
        }
    }
}

//获取移动端二级导航链接
function subnav($name)
{
    $curname = substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '-'));
    $typenae = ['zuqiu' => '足球', 'lanqiu' => '篮球'];
    $sublist = [];
    foreach ($typenae as $i => $item) {
        $sublist[] = [
            'title' => $item . $name,
            'link' => $curname . '-' . $i . '/',
            'cur' => strpos($_SERVER['REQUEST_URI'], $i) ? true : false
        ];
    }
    return $sublist;
}

function formatList($list)
{
    $formatdata = ['ywj' => [], 'zbz' => [], 'wks' => []];
    foreach ($list as $i => $item) {
        $status_name = getzt($item['status_id'], 1, $item['sphere_type']);
        switch ($status_name) {
            case '赛前分析':
                $formatdata['wks'][] = $item;
                break;
            case '锦集/录像':
                $formatdata['ywj'][] = $item;
                break;
            case '直播中...':
                $formatdata['zbz'][] = $item;
                break;
        }
    }
    return $formatdata;
}

function getFootballHotComp($limit = 0)
{
    $Competition = new  \app\commonModel\FootballCompetition();
    return $Competition->getHotData($limit);
}

function getBasketballHotComp($limit = 0)
{
    $Competition = new  \app\commonModel\BasketballCompetition();
    return $Competition->getHotData($limit);
}

function getHotComp($limit = 9)
{
    $halfLimit = ceil($limit / 2);
    $basketballComp = getBasketballHotComp(intval($halfLimit));
    $otherLimit = $limit - count($basketballComp);
    $footballComp = getFootballHotComp(intval($otherLimit));

    $data = array_merge($basketballComp, $footballComp);
    //排序
    array_multisort(array_column($data, 'sort'), SORT_DESC, $data);
    return $data;
}

/**
 *资讯
 * 1:足球2：篮球,0所有
 * */
function getZiXun($cate_id = 0, $competition_id = 0, $limit = 5)
{
    $key = "zinxun:" . $cate_id . '_' . $limit . "_" . $competition_id;
    $data = Cache::store('redis')->get($key);
    if ($data) {
        return $data;
    }
    $model = (new \app\commonModel\Article());
    $list = $model->where("status", 1)->where("delete_time", 0);
    $foot_cate = (new \app\commonModel\ArticleCate())->getFootCate();
    if ($cate_id) {
        if ($cate_id == 1) {
            $list = $list->where("cate_id", 'in', $foot_cate);
        } else if ($cate_id == 2) {
            $basket_cate = (new \app\commonModel\ArticleCate())->getBasketCate();
            $list = $list->where("cate_id", 'in', $basket_cate);
        } else {
            $list = $list->where("cate_id", $cate_id);
        }
    }
    if ($competition_id) {
        //$list = $list->where("competition_id", $competition_id);
    }
    $data = $list->order("id desc ")
        //->field("id,title,cate_id")
        ->limit($limit)
        ->select()
        ->toArray();
    foreach ($data as $k => $v) {
        $data[$k]['short_name_zh'] = in_array($v['cate_id'], $foot_cate) ? '足球' : '篮球';;
        $data[$k]['short_name_py'] = in_array($v['cate_id'], $foot_cate) ? 'zuqiu' : 'lanqiu';
        $competition = $model->getArticleCompetition($v);
        if ($competition) {
            $data[$k]['short_name_zh'] = $competition['short_name_zh'];
            $data[$k]['short_name_py'] = $competition['short_name_py'];
        }
        $data[$k]['cate_id'] = in_array($v['cate_id'], $foot_cate) ? 1 : 2;
    }
    Cache::store('redis')->set($key, $data, 300);
    return $data;
}

/**
 *录像集锦数据
 * type:1集锦，2录像
 * video_type:0足球，1篮球
 * */
function getLuxiangJijin($type, $video_type, $competition_id = 0, $limit = 5, $source = true)
{
    $key = "matchVedio" . $type . "_" . $video_type . "_" . $limit . "_" . $competition_id;
    $data = Cache::store('common_redis')->get($key);
    if ($data) {
        return ['source' => $source, 'data' => $data];
    }
    $model = (new \app\commonModel\MatchVedio());
    $list = Db::connect('compDataDb')->table("fb_match_vedio")->alias('a')->field("a.*");
    if ($video_type == '0') {
        $list = $list->leftJoin("fb_football_match b", "a.match_id=b.id")->where("video_type", $video_type);
    } else if ($video_type == '1') {
        $list = $list->leftJoin("fb_basketball_match b", "a.match_id=b.id")->where("video_type", $video_type);
    }
    $list = $list->where("a.type", $type);
    if ($competition_id) {
        $list = $list->where("b.competition_id", $competition_id);
    }
    $list->order("a.id desc");
    $data = $list->limit($limit)->select()->toArray();

    if (empty($data) && $competition_id) {
        return getLuxiangJijin($type, $video_type, 0, 5, false);
    }
    foreach ($data as $k => $v) {
        $competition = $model->getCompetitionInfo($v);
        $data[$k]['short_name_py'] = empty($competition['competition']) ? ($v['video_type'] == '0' ? 'zuqiu' : 'lanqiu') : $competition['competition']['short_name_py'];
        $data[$k]['title'] = replaceTitleWeb($v['title']);
    }
    Cache::store('common_redis')->set($key, $data, 300);
    return ['source' => $source, 'data' => $data];
}

/**
 * 积分榜
 * @param $limit
 * @param $type
 * @param $compId
 * @return array
 * @throws \think\db\exception\DataNotFoundException
 * @throws \think\db\exception\DbException
 * @throws \think\db\exception\ModelNotFoundException
 */
function getCompTables($limit = 5, $type = 0, $compId = 0): array
{

    if ($compId > 0) {
        $compIds[] = $compId;
    } else {
        //获取联赛
        switch ($type) {
            case 1:
                $hotComp = getBasketballHotComp($limit);
                break;
            default:
                $hotComp = getFootballHotComp($limit);
                break;
        }
        $compIds = array_column($hotComp, 'id');
    }

    //获取积分榜数据
    $stat = \app\commonModel\CompTables::field('comp_id,tables')->where(['type' => $type, 'comp_id' => $compIds])->select()->toArray();

    $compStatModel = new \app\commonModel\FootballCompetitionCount();
    $data = [];
    foreach ($stat as $item) {
        $data[] = $compStatModel->formatFootballCompCount(json_decode($item['tables'], true), $item['comp_id'], $type);
    }

    return $data;
}

function getLive($limit = 5, $type = 'zuqiu', $compId = 0)
{
    $origin = true;
    switch ($type) {
        case 'zuqiu':
            $data = (new app\commonModel\FootballMatch())->getCompetitionListInfo($compId, $limit);
            if (count($data) <= 0) {
                goto defaultCase;
            }
            break;
        case 'lanqiu' :
            $data = (new app\commonModel\BasketballMatch())->getCompetitionListInfo($compId, $limit);
            if (count($data) <= 0) {
                goto defaultCase;
            }
            break;
        default :
            defaultCase:
            $halfLimit = ceil($limit / 2);
            $basketball = (new app\commonModel\BasketballMatch())->getCompetitionListInfo(0, $halfLimit);
            $otherLimit = $limit - count($basketball);
            $football = (new app\commonModel\FootballMatch())->getCompetitionListInfo(0, $otherLimit);
            $data = array_merge($basketball, $football);
            $origin = false;
            break;
    }

    return [
        'origin' => $origin,
        'data' => $data
    ];
}

function getMainMatchLive()
{
    $footballCompetition = new  \app\commonModel\MatchliveLink();
    return $footballCompetition->getList();
}

function getHotTeam($limit = 10, $type = '', $compId = 0)
{
    $basketballTeamModel = new \app\commonModel\BasketballTeam();
    $footballTeamModel = new \app\commonModel\FootballTeam();

    switch ($type) {
        case 'lanqiu' :
            $data = $basketballTeamModel->getTeamByComp($limit, $compId, $type);
            break;
        case 'zuqiu' :
            $data = $footballTeamModel->getTeamByComp($limit, $compId, $type);
            break;
        default :
            $halfLimit = $limit / 2;
            $basketballTeam = $basketballTeamModel->getHotData($halfLimit);
            $otherLimit = $limit - count($basketballTeam);
            $footballTeam = $footballTeamModel->getHotData($otherLimit);
            $data = array_merge($basketballTeam, $footballTeam);
    }

    return $data;
}


function getKeywords()
{
    $key = "keywords";
    $data = Cache::store('redis')->get($key);
    if ($data) {
        return $data;
    }
    $data = (new \app\commonModel\Keywords())->getHot();
    Cache::store('redis')->set($key, $data, 300);
    return $data;
}

/**
 * 替换[]中的内容
 * */
function replaceTitleWeb($str)
{
    $first = '';
    if (preg_match('/国语/', $str)) {
        $first = '[国语]';
    }
    if (preg_match('/原声/', $str)) {
        $first = '[原声]';
    }
    $start = stripos($str, "[");
    $end = stripos($str, "]") + 1;
    return $first . substr_replace($str, '', $start, $end);

}


/**
 * 按分页获取match_vedio
 * */
function getMatchVedio($where = [])
{
    $param = get_params();
    if (count($param) >= 1) {
        $endParmas = end($param);
        $pageParmas = explode('_', $endParmas);
        if ($pageParmas[0] == 'index') {
            //删除参数中最后一个
            array_pop($param);
            $param['page'] = intval($pageParmas[1]);
        }
    }


    $short_name_zh = '';
    $competition_id = 0;
    $param['page'] = (isset($param['page']) && $param['page']) ? $param['page'] : 1;
    $param['limit'] = 15;
    $compName = (isset($param['compname']) && $param['compname']) ? $param['compname'] : '';
    $model = new \app\commonModel\MatchVedio();
    if ($compName) {
        if (isset($where['video_type'])) {
            if ($where['video_type'] == '0') {
                $comp = \app\commonModel\FootballCompetition::where(['short_name_py' => $compName])->find();
                if ($comp) {
                    $where['match_id'] = \app\commonModel\FootballMatch::where(["competition_id" => $comp->id])->column("id");
                    $competition_id = $comp->id;
                    $short_name_zh = $comp->short_name_zh;
                }
            } else {
                $comp = \app\commonModel\BasketballCompetition::where(['short_name_py' => $compName])->find();
                if ($comp) {
                    $where['match_id'] = \app\commonModel\BasketballMatch::where(["competition_id" => $comp->id])->column("id");
                    $competition_id = $comp->id;
                    $short_name_zh = $comp->short_name_zh;
                }
            }
        }
    }
    $list = $model->getList($where, $param);
//    var_dump($param);
//    echo \app\commonModel\MatchVedio::getLastSql();exit;
    foreach ($list['data'] as $k => $v) {
        $list['data'][$k]['date'] = '';
        $list['data'][$k]['teamArr'] = [];
        $competition = $model->getCompetitionInfo($v);
        if (isset($competition['match']['match_time'])) {
            $list['data'][$k]['date'] = date('m-d', $competition['match']['match_time']);
        }
        if ($competition['home_team']) {
            $list['data'][$k]['teamArr'][] = $competition['home_team'];
        }
        if ($competition['away_team']) {
            $list['data'][$k]['teamArr'][] = $competition['away_team'];
        }
        $list['data'][$k]['short_name_py'] = empty($competition['competition']) ? ($v['video_type'] == '0' ? 'zuqiu' : 'lanqiu') : $competition['competition']['short_name_py'];
        $list['data'][$k]['short_name_zh'] = empty($competition['competition']) ? '' : $competition['competition']['short_name_zh'];
        $list['data'][$k]['title'] = replaceTitleWeb($v['title']);
    }
    //$list['current_page'] = $param['page'];
    return [$list, $competition_id, $param, $short_name_zh];
}


/**
 * 获取集锦、录像详情
 * */
function getMatchVedioById($matchId)
{
    $model = new \app\commonModel\MatchVedio();

    $comp = $model->where('id', $matchId)->findOrEmpty();
    if ($comp->isEmpty()) {
        throw new \think\exception\HttpException(404, '找不到页面');
    }
    $matchLive = $model->where(['id' => $matchId])->find()->toArray();
    if ($matchLive['video_type'] == 1) {
        $match = (new \app\commonModel\BasketballMatch())->getMatchInfo("id=" . $matchLive['match_id'], [], 1);
    } else {
        $match = (new \app\commonModel\FootballMatch())->getMatchInfo("id=" . $matchLive['match_id'], [], 1);
    }
    $competition_id = 0;
    $matchLive['team'] = [];
    $matchLive['match_time'] = '';
    $matchLive['short_name_zh'] = '';
    $matchLive['short_name_py'] = '';
    if ($match) {
        $competition_id = $match[0]['competition_id'];
        $matchLive['team'] = [
            'home_team' => [
                'name_zh' => $match[0]['home_team_text'],
                'id' => $match[0]['home_team_id'],
            ],
            'away_team' => [
                'name_zh' => $match[0]['away_team_text'],
                'id' => $match[0]['away_team_id'],
            ]
        ];
        $matchLive['match_time'] = date('Y-m-d', $match[0]['match_time']);
        $matchLive['short_name_zh'] = $match[0]['competition_text'];
        $matchLive['short_name_py'] = $match[0]['comp_py'];
        $matchLive['title'] = replaceTitleWeb($matchLive['title']);
    }
    return [$matchLive, $competition_id];
}