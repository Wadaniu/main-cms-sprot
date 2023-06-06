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
    $article = \think\facade\Db::name('article')
        ->where($map)
        ->field("id,competition_id,title,cate_id")
        ->order("id desc")
        ->cache(true, 300)
        ->find();
    if(!$article){
        return [];
    }
    if($article['cate_id']==1){
        $competition = (new \app\commonModel\FootballCompetition())->where("id",$article['competition_id'])->find();
    }else{
        $competition = (new \app\commonModel\BasketballCompetition())->where("id",$article['competition_id'])->find();
    }
    if(!$competition){
        $article['short_name_py'] = '';
    }else{
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
    $article = \think\facade\Db::name('article')
        ->where($map)
        ->field("id,competition_id,title,cate_id")
        ->order("id asc")
        ->cache(true, 300)
        ->find();
    if(!$article){
        return [];
    }
    if($article['cate_id']==1){
        $competition = (new \app\commonModel\FootballCompetition())->where("id",$article['competition_id'])->find();
    }else{
        $competition = (new \app\commonModel\BasketballCompetition())->where("id",$article['competition_id'])->find();
    }
    if(!$competition){
        $article['short_name_py'] = '';
    }else{
        $article['short_name_py'] = $competition['short_name_py'];
    }

    return $article;
}

function getzt($id, $type): string
{
    $back = '';
    $zt = [0, 1, 9, 10, 11, 12, 13];
    $boolean = in_array($id, $zt);

    switch ($type) {
        case 0:
            $back = $boolean ? 'icon-fenxi' : ($id == 8 ? 'icon-bofang' : 'icon-zhibo');
            break;
        case 1:
            $back = $boolean ? '赛前分析' : ($id == 8 ? '锦集/录像' : '直播中...');
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

function moresrc($name)
{
    return '/' . $name . '/' . (strpos(get_ruleName(), 'zuqiu') ? 'zuqiu/' : (strpos(get_ruleName(), 'lanqiu') ? 'lanqiu/' : '')) . (get_params('compname') ? ($name == 'live' ? '' : '1/') . get_params('compname') : '');
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
    $cururl = $_SERVER['REQUEST_URI'];
    $alllink = count(get_params()) ? substr($cururl, 0, findIndex($cururl, '/', 3) + 1) : $cururl;
    $typelist[] = ['title' => '全部', 'src' => $alllink];
    $typedata = strpos($cururl, 'zuqiu') ? getFootballHotComp() : getBasketballHotComp();
    $page = strpos($cururl, 'live') ? '' : '1/';
    foreach ($typedata as $item) {
        $typelist[] = [
            'title' => $item['short_name_zh'],
            'src' => $alllink . $page . $item['short_name_py'] . '/'
        ];
    }
    return $typelist;
}

//全部热门类别
function hotlive($src, $name = ''): array
{

    $typelist = [];
    $hottype = [getFootballHotComp(), getBasketballHotComp()];

    foreach ($hottype as $key => $type) {
        $typesrc = $key ? 'lanqiu' : 'zuqiu';
        foreach ($type as $item) {
            $typelist[] = [
                'id' => $item['id'],
                'type' => $typesrc,
                'title' => $item['short_name_zh'] . $name,
                'src' => '/' . $src . '/' . $typesrc . '/' . $item['short_name_py']
            ];
        }
    }

    return $typelist;
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

    return array_merge($basketballComp, $footballComp);
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
    $list = $model->where("status", 1);
    if ($cate_id) {
        $list = $list->where("cate_id", $cate_id);
    }
    if ($competition_id) {
        $list = $list->where("competition_id", $competition_id);
    }
    $data = $list->order("id desc ")
        //->field("id,title,cate_id")
        ->limit($limit)
        ->select()
        ->toArray();
    foreach ($data as $k => $v) {
        $data[$k]['short_name_zh'] = '';
        $data[$k]['short_name_py'] = $v['cate_id'] == '1' ? 'zuqiu' : 'lanqiu';
        $competition = $model->getArticleCompetition($v["id"]);
        if ($competition) {
            $data[$k]['short_name_zh'] = $competition['short_name_zh'];
            $data[$k]['short_name_py'] = $competition['short_name_py'];
        }
    }
    Cache::store('redis')->set($key, $data, 300);
    return $data;
}

/**
 *录像集锦数据
 * type:1集锦，2录像
 * video_type:0足球，1篮球
 * */
function getLuxiangJijin($type, $video_type, $competition_id = 0, $limit = 5)
{
    $key = "matchVedio" . $type . "_" . $video_type . "_" . $limit . "_" . $competition_id;
    $data = Cache::store('common_redis')->get($key);
    if ($data) {
        return $data;
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
    foreach ($data as $k => $v) {
        $competition = $model->getCompetitionInfo($v['id']);
        $data[$k]['short_name_py'] = empty($competition['competition']) ? ($v['video_type'] == '0' ? 'zuqiu' : 'lanqiu') : $competition['competition']['short_name_py'];
    }
    Cache::store('common_redis')->set($key, $data, 300);
    return $data;
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
function getCompTables($limit = 5, $type = 'zuqiu', $compId = 0)
{

    if ($compId > 0) {
        $compIds[] = $compId;
    } else {
        //获取联赛
        switch ($type) {
            case 'lanqiu':
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
        $data[] = $compStatModel->formatFootballCompCount(json_decode($item['tables'], true), $item['comp_id']);
    }

    return $data;
}

function getLive($limit = 5, $type = 'zuqiu', $compId = 0)
{

    switch ($type) {
        case 'zuqiu':
            $data = (new app\commonModel\FootballMatch())->getCompetitionListInfo($compId, $limit);
            break;
        case 'lanqiu' :
            $data = (new app\commonModel\BasketballMatch())->getCompetitionListInfo($compId, $limit);
            break;
        default :
            $halfLimit = ceil($limit / 2);
            $basketball = (new app\commonModel\BasketballMatch())->getCompetitionListInfo($compId, $halfLimit);
            $otherLimit = $limit - count($basketball);
            $football = (new app\commonModel\FootballMatch())->getCompetitionListInfo($compId, $otherLimit);
            $data = array_merge($basketball, $football);
            break;
    }
    return $data;
}

function getMainMatchLive()
{
    $footballCompetition = new  \app\commonModel\MatchliveLink();
    return $footballCompetition->getList();
}

function getHotTeam($limit = 10,$type = '',$compId = 0)
{
    $basketballTeamModel = new \app\commonModel\BasketballTeam();
    $footballTeamModel = new \app\commonModel\FootballTeam();

    switch ($type){
        case 'lanqiu' :
            $data = $basketballTeamModel->getTeamByComp($limit,$compId,$type);
            break;
        case 'zuqiu' :
            $data = $footballTeamModel->getTeamByComp($limit,$compId,$type);
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
function replaceTitleWeb($str){
    $start = stripos($str,"[")+1;
    $end = stripos($str,"]")-1;
    return substr_replace($str,'****',$start,$end);
}



/**
 * 按分页获取match_vedio
 * */
function getMatchVedio($where=[]){
    $param = get_params();
    $competition_id = 0;
    $param['page'] = (isset($param['page']) && $param['page'])?$param['page']:1;
    $compName = (isset($param['compname']) &&  $param['compname'])?$param['compname']:'';
    $model = new \app\commonModel\MatchVedio();
    if($compName){
        if(isset($where['video_type'])){
            if($where['video_type']=='0'){
                $comp = \app\commonModel\FootballCompetition::where(['short_name_py'=>$compName])->find();
                if($comp){
                    $where['match_id'] = \app\commonModel\FootballMatch::where(["competition_id"=>$comp->id])->column("id");
                    $competition_id = $comp->id;
                }
            }else{
                $comp = \app\commonModel\BasketballCompetition::where(['short_name_py'=>$compName])->find();
                if($comp){
                    $where['match_id'] = \app\commonModel\BasketballMatch::where(["competition_id"=>$comp->id])->column("id");
                    $competition_id = $comp->id;
                }
            }
        }
    }
    $list = $model->getList($where,$param)->toArray();
    foreach ($list['data'] as $k=>$v){
        $list['data'][$k]['date']='';
        $list['data'][$k]['teamArr'] = [];
        $competition = $model->getCompetitionInfo($v['id']);
        if(isset($competition['match']['match_time'])){
            $list['data'][$k]['date'] = date('m-d',$competition['match']['match_time']);
        }
        if($competition['home_team']){
            $list['data'][$k]['teamArr'][] = $competition['home_team'];
        }
        if($competition['away_team']){
            $list['data'][$k]['teamArr'][] = $competition['away_team'];
        }
        $list['data'][$k]['short_name_py'] = empty($competition['competition'])?($v['video_type']=='0'?'zuqiu':'lanqiu'):$competition['competition']['short_name_py'];
    }
    return [$list,$competition_id,$param];
}