<?php

namespace app\home\controller;

use app\commonModel\Article;
use app\commonModel\ArticleKeywords;
use app\commonModel\BasketballCompetition;
use app\commonModel\BasketballMatch;
use app\commonModel\BasketballTeam;
use app\commonModel\FootballCompetition;
use app\commonModel\FootballMatch;
use app\commonModel\FootballTeam;
use app\commonModel\Keywords;
use app\commonModel\MatchVedio;
use app\exception\Sitemap as SitemapVendor;
use DOMDocument;
use think\facade\Request;
use think\facade\Route;
use XSLTProcessor;

class Sitemap
{
    function createSitemap() {
        $sitemap = new SitemapVendor(Request::host());
        $routeList = Route::getRuleList();

        $existFootballMatchIdMap = [];
        $existBasketballMatchIdMap = [];

        //获取热门联赛
        $basketballComp = getBasketballHotComp();
        $footballComp = getFootballHotComp();

        $footballMatchModel = new FootballMatch();
        $basketballMatchModel = new BasketballMatch();
        $matchVideo = new MatchVedio();
        $articleModel = new Article();

        foreach ($basketballComp as $comp){
            $matchIdArr = $basketballMatchModel->where('match_time','>=',time()-86400)->where('competition_id',$comp['id'])->limit(1000)->column('id');
            if (empty($matchIdArr)){
                continue;
            }
            $existBasketballMatchIdMap[$comp['id']] = $matchIdArr;
        }

        foreach ($footballComp as $comp){
            $matchIdArr = $footballMatchModel->where('match_time','>=',time()-86400)->where('competition_id',$comp['id'])->limit(1000)->column('id');
            if (empty($matchIdArr)){
                continue;
            }
            $existFootballMatchIdMap[$comp['id']] = $matchIdArr;
        }
        //遍历路由
        foreach ($routeList as $route){
            switch ($route['name']){
                case '/':
                    $sitemap->addItem('/', '1.0', 'daily', date('Y-m-d H:i:s'));
                    break;
                case '/live-zuqiu/':
                    //足球直播
                    $sitemap->addItem($route['name'], '0.8', 'daily', date('Y-m-d H:i:s'));
                    foreach ($footballComp as $comp){
                        //联赛直播
                        $sitemap->addItem($route['name'].$comp['short_name_py'].'/', '0.8', 'daily', date('Y-m-d H:i:s'));

                        if (!isset($existFootballMatchIdMap[$comp['id']])){
                            continue;
                        }
                        $matchIdArr = $existFootballMatchIdMap[$comp['id']];
                        //赛程详情
                        foreach ($matchIdArr as $id){
                            $sitemap->addItem($route['name'].$comp['short_name_py'].'/'.$id.'.html', '0.6', 'daily', date('Y-m-d H:i:s'));
                        }
                    }
                    break;
                case '/live-lanqiu/':
                    //篮球直播
                    $sitemap->addItem($route['name'], '0.8', 'daily', date('Y-m-d H:i:s'));
                    foreach ($basketballComp as $comp){
                        //联赛直播
                        $sitemap->addItem($route['name'].$comp['short_name_py'].'/', '0.8', 'daily', date('Y-m-d H:i:s'));

                        if (!isset($existBasketballMatchIdMap[$comp['id']])){
                            continue;
                        }
                        $matchIdArr = $existBasketballMatchIdMap[$comp['id']];
                        //赛程详情
                        foreach ($matchIdArr as $id){
                            $sitemap->addItem($route['name'].$comp['short_name_py'].'/'.$id.'.html', '0.6', 'daily', date('Y-m-d H:i:s'));
                        }
                    }
                    break;
                case '/luxiang-zuqiu/':
                    $sitemap->addItem($route['name'], '0.8', 'daily', date('Y-m-d H:i:s'));

                    foreach ($footballComp as $comp){
                        //联赛录像
                        $sitemap->addItem($route['name'].$comp['short_name_py'].'/', '0.8', 'daily', date('Y-m-d H:i:s'));

                        if (!isset($existFootballMatchIdMap[$comp['id']])){
                            continue;
                        }
                        $matchIdArr = $existFootballMatchIdMap[$comp['id']];

                        $matchVideoIds = $matchVideo->where('match_id','in',$matchIdArr)->where('type',2)->where('video_type',0)->column('id');
                        if (empty($matchVideoIds)){
                            continue;
                        }
                        //录像详情
                        foreach ($matchVideoIds as $id){
                            $sitemap->addItem($route['name'].$comp['short_name_py'].'/'.$id.'.html', '0.6', 'daily', date('Y-m-d H:i:s'));
                        }
                    }
                    break;
                case '/luxiang-lanqiu/':
                    $sitemap->addItem($route['name'], '0.8', 'daily', date('Y-m-d H:i:s'));

                    foreach ($basketballComp as $comp){
                        //联赛录像
                        $sitemap->addItem($route['name'].$comp['short_name_py'].'/', '0.8', 'daily', date('Y-m-d H:i:s'));
                        if (!isset($existBasketballMatchIdMap[$comp['id']])){
                            continue;
                        }
                        $matchIdArr = $existBasketballMatchIdMap[$comp['id']];
                        $matchVideoIds = $matchVideo->where('match_id','in',$matchIdArr)->where('type',2)->where('video_type',1)->column('id');
                        if (empty($matchVideoIds)){
                            continue;
                        }
                        //录像详情
                        foreach ($matchVideoIds as $id){
                            $sitemap->addItem($route['name'].$comp['short_name_py'].'/'.$id.'.html', '0.6', 'daily', date('Y-m-d H:i:s'));
                        }
                    }
                    break;
                case '/jijin-zuqiu/':
                    $sitemap->addItem($route['name'], '0.8', 'daily', date('Y-m-d H:i:s'));

                    foreach ($footballComp as $comp){
                        //联赛录像
                        $sitemap->addItem($route['name'].$comp['short_name_py'].'/', '0.8', 'daily', date('Y-m-d H:i:s'));
                        if (!isset($existFootballMatchIdMap[$comp['id']])){
                            continue;
                        }
                        $matchIdArr = $existFootballMatchIdMap[$comp['id']];
                        $matchVideoIds = $matchVideo->where('match_id','in',$matchIdArr)->where('type',1)->where('video_type',0)->column('id');
                        if (empty($matchVideoIds)){
                            continue;
                        }
                        //录像详情
                        foreach ($matchVideoIds as $id){
                            $sitemap->addItem($route['name'].$comp['short_name_py'].'/'.$id.'.html', '0.6', 'daily', date('Y-m-d H:i:s'));
                        }
                    }
                    break;
                case '/jijin-lanqiu/':
                    $sitemap->addItem($route['name'], '0.8', 'daily', date('Y-m-d H:i:s'));

                    foreach ($basketballComp as $comp){
                        //联赛录像
                        $sitemap->addItem($route['name'].$comp['short_name_py'].'/', '0.8', 'daily', date('Y-m-d H:i:s'));
                        if (!isset($existBasketballMatchIdMap[$comp['id']])){
                            continue;
                        }
                        $matchIdArr = $existBasketballMatchIdMap[$comp['id']];
                        $matchVideoIds = $matchVideo->where('match_id','in',$matchIdArr)->where('type',1)->where('video_type',1)->column('id');
                        if (empty($matchVideoIds)){
                            continue;
                        }
                        //录像详情
                        foreach ($matchVideoIds as $id){
                            $sitemap->addItem($route['name'].$comp['short_name_py'].'/'.$id.'.html', '0.6', 'daily', date('Y-m-d H:i:s'));
                        }
                    }
                    break;
                case '/zixun-zuqiu/':
                    $sitemap->addItem($route['name'], '0.8', 'daily', date('Y-m-d H:i:s'));

                    $aids = $articleModel->where('cate_id',1)->order('id','desc')->limit(1000)->column('id');
                    //录像详情
                    foreach ($aids as $id){
                        $sitemap->addItem($route['name'].$id.'.html', '0.6', 'daily', date('Y-m-d H:i:s'));
                    }
                    break;
                case '/zixun-lanqiu/':
                    $sitemap->addItem($route['name'], '0.8', 'daily', date('Y-m-d H:i:s'));

                    $aids = $articleModel->where('cate_id',2)->order('id','desc')->limit(1000)->column('id');
                    //录像详情
                    foreach ($aids as $id){
                        $sitemap->addItem($route['name'].$id.'.html', '0.6', 'daily', date('Y-m-d H:i:s'));
                    }
                    break;
                case '/liansai-zuqiu/':
                    //获取所有联赛统计数
                    $count = FootballCompetition::count();
                    $pageMax = ceil($count / 24);
                    $pageMax = $pageMax > 1000 ? 1000 : $pageMax;
                    for ($i = 1;$i <= $pageMax;$i++){
                        $sitemap->addItem($route['name'].'index_'.$i.'/', '0.8', 'daily', date('Y-m-d H:i:s'));
                    }
                    break;
                case '/liansai-lanqiu/':
                    //获取所有联赛统计数
                    $count = BasketballCompetition::count();
                    $pageMax = ceil($count / 24);
                    $pageMax = $pageMax > 1000 ? 1000 : $pageMax;
                    for ($i = 1;$i <= $pageMax;$i++){
                        $sitemap->addItem($route['name'].'index_'.$i.'/', '0.8', 'daily', date('Y-m-d H:i:s'));
                    }
                    break;
                case '/qiudui-zuqiu/':
                    $count = FootballTeam::count();
                    $pageMax = ceil($count / 24);
                    $pageMax = $pageMax > 1000 ? 1000 : $pageMax;
                    for ($i = 1;$i <= $pageMax;$i++){
                        $sitemap->addItem($route['name'].'index_'.$i.'/', '0.8', 'daily', date('Y-m-d H:i:s'));
                    }
                    break;
                case '/qiudui-lanqiu/':
                    $count = BasketballTeam::count();
                    $pageMax = ceil($count / 24);
                    $pageMax = $pageMax > 1000 ? 1000 : $pageMax;
                    for ($i = 1;$i <= $pageMax;$i++){
                        $sitemap->addItem($route['name'].'index_'.$i.'/', '0.8', 'daily', date('Y-m-d H:i:s'));
                    }
                    break;
                case '/jifen-lanqiu/':
                    foreach ($basketballComp as $comp){
                        //联赛直播
                        $sitemap->addItem($route['name'].$comp['short_name_py'], '0.8', 'daily', date('Y-m-d H:i:s'));
                    }
                    break;
                case '/jifen-zuqiu/':
                    foreach ($footballComp as $comp){
                        //联赛直播
                        $sitemap->addItem($route['name'].$comp['short_name_py'], '0.8', 'daily', date('Y-m-d H:i:s'));
                    }
                    break;
                case '/yuce-lanqiu/':
                    $sitemap->addItem($route['name'], '0.8', 'daily', date('Y-m-d H:i:s'));
                    $basketballMatchForcastIds = $basketballMatchModel->where("match_time",">",time())->where("forecast",'not null')->limit(1000)->column("id");
                    if(!empty($basketballMatchForcastIds)){
                        foreach ($basketballMatchForcastIds as $id){
                            $sitemap->addItem($route['name'].$id.'.html', '0.6', 'daily', date('Y-m-d H:i:s'));
                        }
                    }
                    break;
                case '/yuce-zuqiu/':
                    $sitemap->addItem($route['name'], '0.8', 'daily', date('Y-m-d H:i:s'));
                    $basketballMatchForcastIds = $footballMatchModel->where("match_time",">",time())->where("forecast",'not null')->limit(1000)->column("id");
                    if(!empty($basketballMatchForcastIds)){
                        foreach ($basketballMatchForcastIds as $id){
                            $sitemap->addItem($route['name'].$id.'.html', '0.6', 'daily', date('Y-m-d H:i:s'));
                        }
                    }
                    break;

            }
        }

        $sitemap->endSitemap();
        $this->createXSL2Html("sitemap.xml", "sitemap-xml.xsl", "sitemap.html", true);
    }

    function createXSL2Html($xmlFile, $xslFile, $htmlFile, $isopen_htmlfile=false) {

        header("Content-Type: text/html; charset=UTF-8");
        $xml = new DOMDocument();
        $xml->Load($xmlFile);
        $xsl = new DOMDocument();
        $xsl->Load($xslFile);
        $xslproc = new XSLTProcessor();
        $xslproc->importStylesheet($xsl);
// 	echo $xslproc->transformToXML($xml);

        $f = fopen($htmlFile, 'w+');
        fwrite($f, $xslproc->transformToXML($xml));
        fclose($f);

        // 是否打开生成的文件 sitemap.html
        if($isopen_htmlfile) {
            echo "<script>window.open('".$htmlFile."')</script>";
            echo "<br>Create sitemap.html Success";
        }
    }
}