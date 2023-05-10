<?php

namespace app\home\controller;

use app\exception\Sitemap as SitemapVendor;
use DOMDocument;
use think\facade\Db;
use think\facade\Env;
use \think\facade\Request;
use think\facade\Route;
use XSLTProcessor;

class Sitemap
{
    const GPriorityArray = array("1"=>"1.0", "2"=>"0.8", "3"=>"0.6", "4"=>"0.5");				// 按照层级对应优先级，第一层优先级为1，第二级为0.8，第三级为0.6
    const PlayBackLimit = 60;

    const INFO_INDEX_TITLE = [
        'sqfx'  =>  '赛前分析',
        'zbxx'  =>  '直播信息',
        'jstj'  =>  '技术统计',
        'jjlx'  =>  '集锦录像'
    ];
    function createSitemap() {
        $sitemap = new SitemapVendor(Request::host());
        $routeList = Route::getRuleList();

        $teamIdArr = [];
        $compIdArr = [];
        $matchIdArr = [];
        $aidArr = [];

        //遍历路由
        foreach ($routeList as $route){
            switch ($route['name']){
                case '/':
                    $sitemap->addItem('/', '1.0', 'daily', date('Y-m-d H:i:s'));
                    break;
                case 'integral':
                    $sitemap->addItem('/integral.html', '0.8', 'daily', date('Y-m-d H:i:s'));
                    break;
                case 'playerdata':
                    $sitemap->addItem('/playerdata/shooter.html','0.8','daily', date('Y-m-d H:i:s'));
                    $sitemap->addItem('/playerdata/assists.html','0.8','daily', date('Y-m-d H:i:s'));
                    break;
                case 'comp':
                    //获取热门联赛id
                    $footballCompetition = new  \app\admin\model\FootballCompetition();
                    $footballHotData = $footballCompetition->getHotData();
                    $model = new \app\admin\model\FootballMatch();
                    //热门联赛ids
                    $compIds = array_column($footballHotData,'id');
                    $compIdArr = array_merge($compIdArr,$compIds);
                    //获取赛程
                    $match = $model->getWeekData($compIds);
                    //赛程ids
                    $matchIds = array_column($match,'id');
                    $matchIdArr = array_unique(array_merge($matchIdArr,$matchIds));
                    //队伍ids
                    $homeTeamIds = array_column($match,'home_team_id');
                    $awayTeamIds = array_column($match,'away_team_id');
                    $teamIdArr = array_unique(array_merge($teamIdArr,$homeTeamIds,$awayTeamIds));

                    //获取各个联赛下赛程
                    foreach ($compIds as $compId){
                        $match = $model->getFootballMatchIndex($compId);
                        //赛程ids
                        $matchIds = array_column($match,'id');
                        $matchIdArr = array_unique(array_merge($matchIdArr,$matchIds));
                        //队伍ids
                        $homeTeamIds = array_column($match,'home_team_id');
                        $awayTeamIds = array_column($match,'away_team_id');
                        $teamIdArr = array_unique(array_merge($teamIdArr,$homeTeamIds,$awayTeamIds));
                    }
                    break;
                case 'news':
                    //新闻列表
                    $aidArr = Db::name('article')->where('delete_time',0)->column('id');
                    $page = ceil(count($aidArr) / get_config('app.page_size'));
                    for ($i = 1;$i <= $page; $i++){
                        $sitemap->addItem("/news/$i.html",'0.8','always', date('Y-m-d H:i:s'));
                    }
                    //获取所有标签页
                    $keywordsModel = new \app\admin\model\Keywords();
                    $keywords = $keywordsModel->getHot();
                    foreach ($keywords as $keyword){
                        $count = Db::name('article_keywords')->where(['status'=>1,'keywords_id'=>$keyword['id']])->count();
                        $page = ceil($count / get_config('app.page_size'));
                        for ($i = 1;$i <= $page; $i++){
                            $sitemap->addItem("/news/$i/".$keyword['id'].".html",'0.8','always', date('Y-m-d H:i:s'));
                        }
                    }
                    break;
                case 'playback':

                    $endDate = '';
                    $startDate = '';

                    //获取近两个月回放数据
                    for ($i=self::PlayBackLimit;$i >= 0; $i--){
                        $date = date('Ymd',strtotime("-$i day"));
                        if (empty($compIdArr)){
                            $compId = Env::get('Home.HOME_SPACE');
                            $sitemap->addItem("/playback/$compId/$date.html",'0.8','always', date('Y-m-d H:i:s'));
                        }else{
                            foreach ($compIdArr as $compId){
                                $sitemap->addItem("/playback/$compId/$date.html",'0.8','always', date('Y-m-d H:i:s'));
                            }
                        }

                        //取开始时间和结束时间
                        if ($i == self::PlayBackLimit){
                            $startDate = $date;
                        }elseif ($i == 0){
                            $endDate = $date;
                        }
                    }

                    //获取近两个月赛程
                    $model = new \app\admin\model\FootballMatch();
                    //获取赛程
                    $match = $model->getMatchByDate([Env::get('HOME.HOME_SPACE')],$startDate,$endDate);
                    //赛程ids
                    $matchIds = array_column($match,'id');
                    $matchIdArr = array_unique(array_merge($matchIdArr,$matchIds));
                    //队伍ids
                    $homeTeamIds = array_column($match,'home_team_id');
                    $awayTeamIds = array_column($match,'away_team_id');
                    $teamIdArr = array_unique(array_merge($teamIdArr,$homeTeamIds,$awayTeamIds));
                    break;
            }
        }
        //添加联赛
        foreach ($compIdArr as $compId){
            $sitemap->addItem("/comp/$compId.html", '0.8', 'daily',  date('Y-m-d H:i:s'));
        }
        //获取所有文章详情
        foreach ($aidArr as $aid){
            $sitemap->addItem("/newcont/$aid.html",'0.6','always', date('Y-m-d H:i:s'));
        }
        //添加team
        foreach ($teamIdArr as $teamId){
            $sitemap->addItem("/team/$teamId.html", '0.6', 'daily',  date('Y-m-d H:i:s'));
        }
        //添加赛程
        foreach ($matchIdArr as $matchId){
            foreach (self::INFO_INDEX_TITLE as $key=>$item){
                $sitemap->addItem("/info/$key/$matchId.html", '0.6', 'daily',  date('Y-m-d H:i:s'));
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