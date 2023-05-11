<?php
/**
 * @copyright Copyright (c) 2021 勾股工作室
 * @license https://opensource.org/licenses/Apache-2.0
 * @link https://www.gougucms.com
 */
 
declare (strict_types = 1);

namespace app\home\controller;
use app\home\BaseController;
use think\facade\Db;
use think\facade\Env;
use think\facade\View;


class Newadd extends BaseController
{
    //比赛信息title
    const INFO_INDEX_TITLE = [
        'sqfx'  =>  '赛前分析',
        'zbxx'  =>  '直播信息',
        'jstj'  =>  '技术统计',
        'jjlx'  =>  '集锦录像'
    ];

    //球员数据titile
    const PLAY_DATA_TITLE = [
        'shooter'   =>  '射手榜',
        'assists'   =>  '助攻榜'
    ];

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::initialize();
    }

	public function integral(){
        $param = get_params();
        $id = $param['id'] ?? Env::get('Home.HOME_SPACE');

        //获取积分榜
        $model = new \app\commonModel\FootballCompetitionCount();
        $data = $model->getFootballCompCountByCompId($id);
        //var_dump($data[0]['rows']);die;
        View::assign("integral",$data);
	    return view();
	}

	public function news(){

        $param = get_params();
        $id = $param['id'] ?? Env::get('Home.HOME_SPACE');
        $label = $param['label'] ?? 0;
        if ($id > 0){
            //获取列表文章
            $model = new \app\commonModel\Article();
            $where = 'a.competition_id = '.$id;

            if (!empty($label)){
                //获取标签对应关键字id
                $articleKeywordsModel = new \app\commonModel\ArticleKeywords();
                $aid = $articleKeywordsModel->getByKeywordId($label);

                if ($aid){
                    $aidArr = array_column($aid,'aid');
                    $airStr = implode(',',$aidArr);
                    $where = 'a.id IN ('.$airStr.')';
                }

                $keywordsModel = new \app\commonModel\Keywords();
                $keyword = $keywordsModel->find($label);

                $seo = [
                    'title' => $keyword->title. ' - '.$this->webCommonTitle,
                    'keywords' => $keyword->title,
                    'description' => $keyword->title,
                ];
                View::assign('seo',$seo);
            }
            $data = $model->getListByCompId($where,$param);

            //var_dump($data);die;
            View::assign("news",$data);
            View::assign("label",$label);
        }

	    return view();
	}
	
	public function newcont(){
        $param = get_params();
        $id = $param['id'] ?? 0;
        if($id > 0){
            //文章获取
            $articleModel = new \app\commonModel\Article();
            $data = $articleModel->getArticleById($id);
            $keywords = $data->articleKeywords->toArray();

            //获取上一篇下一篇
            $first = $articleModel->getFirstArticle($id);
            $next = $articleModel->getNextArticle($id);

            $seo = [
                'title'   =>    $data->title,
                'keywords'  =>  implode(',',array_column($keywords,'title')),
                'description'   =>  $data->desc
            ];

            //阅读量+1
            $data->read += 1;
            $data->save();

            $aboutArticle = [];
            $existId = [];
            foreach ($keywords as $keyword){
                //获取关键字相关文章
                $article = $articleModel->getByKeyword("aid <> $id",$keyword['id']);
                if (empty($article)){
                    //获取不到相关文章则随机文章
                    $article = $articleModel->getRand();
                }

                foreach ($article as $item){
                    if (in_array($item['id'],$existId)){
                        continue;
                    }
                    $existId[] = $item['id'];
                    $aboutArticle[] = $item;
                }
            }
            View::assign('aboutArticle',$aboutArticle);
            View::assign('seo',$seo);
            View::assign("article",$data);
            View::assign("keywords",$keywords);
            View::assign("first",$first);
            View::assign("next",$next);
        }

	    return view();
	}
	
	public function playerdata(){
        $param = get_params();
        $id = $param['id'] ?? Env::get('Home.HOME_SPACE');

        //获取积分榜
        $model = new \app\commonModel\FootballCompetitionCount();
        //射手榜
        $data['shooter'] = $model->getShootCountByCompId($id,0);
        //助攻榜
        $data['assists'] = $model->getShootCountByCompId($id,1);

        //获取seo
        $nav = Db::name('nav_info')->where('src',$param['name'])->find();

        $seo = [
          'title' =>   $nav['web_title'],
          'keywords' =>   $nav['web_keywords'],
          'description' =>   $nav['web_desc'],
        ];

        View::assign('seo',$seo);
        View::assign("shooter",$data['shooter']);
        View::assign("assists",$data['assists']);
        return view();
	}

    public function playback(){
        $param = get_params();
        $date = $param['date'] ?? date('Ymd',time());

        $id = $param['id'] ?? Env::get('Home.HOME_SPACE');
        $model = new \app\commonModel\FootballMatch();
        //联赛历史七天赛程
        $matchBack = $model->getMatchByDate([$id],$date,$date);

        $dateArr = [];

        //判断是否七天内数据
        $validateDateStart = strtotime("-7 day");
        if (strtotime($date) > (time()+30) || strtotime($date) < $validateDateStart){
            //不在七天内获取
            for($i=0;$i<7;$i++){
                $dateDays = strtotime($date) + $i * 86400;
                $dateArr[] = date('Y-m-d', $dateDays);
            }
        }else{
            for($i=0;$i<7;$i++){
                $days = $i - 6;
                $dateDays = date('Y-m-d', strtotime("$days days"));
                $dateArr[] = $dateDays;
            }
        }

        $cmpModel = new \app\commonModel\FootballCompetition();
        $cmpInfo = $cmpModel->info($id);

        if (!empty($param['id'])){
            //【联赛名】录像回放_【联赛名】视频集锦_【联赛名】比赛视频高清无插件 - 【网站名称】
            $seo['title'] = $cmpInfo->short_name_zh.'录像回放_'.$cmpInfo->short_name_zh.'视频集锦_'.
                $cmpInfo->short_name_zh.'比赛视频高清无插件 - '.$this->webCommonTitle;
            //【联赛名】录像回放,【联赛名】视频集锦,【联赛名】比赛视频高清无插件,【网站名称】
            $seo['keywords'] = $cmpInfo->short_name_zh.'录像回放,'.
                $cmpInfo->short_name_zh.'视频集锦,'.
                $cmpInfo->short_name_zh.'比赛视频高清无插件,'.$this->webCommonTitle;
            //欢迎来到【网站名称】，我们为您提供最全面的【联赛名】录像回放、【联赛名】视频集锦服务，无论是你错过了比赛还是需要重新观看，我们都有高清晰度、无广告插播的录像回放，同时，我们持续更新并提供详细的比赛解说和数据统计，让您身临其境地享受每一分钟的经典比赛。
            $seo['description'] = '欢迎来到'.$this->webCommonTitle.',我们为您提供最全面的'.$cmpInfo->short_name_zh.
                '录像回放、'.$cmpInfo->short_name_zh.'视频集锦服务，无论是你错过了比赛还是需要重新观看，我们都有高清晰度、
                无广告插播的录像回放，同时，我们持续更新并提供详细的比赛解说和数据统计，让您身临其境地享受每一分钟的经典比赛。';

            View::assign('seo',$seo);
        }

        if (!empty($param['date'])){
            //【时间/年月日】【联赛名】录像_【联赛名】比赛视频全场回放 - 【网站名称】
            $seo['title'] = date('Y年m月d日',strtotime($date)).$cmpInfo->short_name_zh.'录像_'.
                $cmpInfo->short_name_zh.'比赛视频全场回放 - '.$this->webCommonTitle;
            //【时间/年月日】【联赛名】录像,【联赛名】比赛视频全场回放,【网站名称】
            $seo['keywords'] = date('Y年m月d日',strtotime($date)).$cmpInfo->short_name_zh.'录像,'.
                $cmpInfo->short_name_zh.'比赛视频全场回放,'.$this->webCommonTitle;
            //欢迎来到【网站名称】，我们为您提供【时间/年月日】最全面的【联赛名】录像回放、【联赛名】视频集锦服务，无论是你错过了比赛还是需要重新观看，我们都有高清晰度、无广告插播的录像回放，同时我们持续更新并提供详细的比赛解说和数据统计，让您身临其境地享受每一分钟的经典比赛。
            $seo['description'] = '欢迎来到'.$this->webCommonTitle.'，我们为您提供'.date('Y年m月d日',strtotime($date)).
                '最全面的'.$cmpInfo->short_name_zh.'录像回放、'.$cmpInfo->short_name_zh.'视频集锦服务，无论是你错过了比赛还是需要重新观看，
                我们都有高清晰度、无广告插播的录像回放，同时我们持续更新并提供详细的比赛解说和数据统计，让您身临其境地享受每一分钟的经典比赛。';

            View::assign('seo',$seo);
        }

        View::assign("match",$matchBack);
        View::assign("dateRange",array_reverse($dateArr));
        return view();
    }
	
	public function playinfo(){

        $param = get_params();
        $path = get_path();
//var_dump($ttt);die;
        $lujin = $param['id'] ?? 0;
        $id = $path[0] ?? 0;

        $temp = array_key_exists($lujin,self::INFO_INDEX_TITLE)?'':'tpl/404.html';

        if ($id > 0){
            //直播
            $model = new \app\commonModel\FootballMatch();
            $matchLive = $model->getMatchLive($id)->toArray();

            if ($matchLive){
                $matchLive['mobile_link'] = json_decode($matchLive['mobile_link']??'',true);
                $matchLive['pc_link'] = json_decode($matchLive['pc_link']??'',true);
            }
            $footballMatchInfoModel = new \app\commonModel\FootballMatchInfo();
            $matchInfo = $footballMatchInfoModel->getByMatchId($id);
            //历史交锋
            $analysis = [
                'info'      =>  is_null($matchInfo['info']) ? [] : json_decode($matchInfo['info'],true),
                'future'    =>  is_null($matchInfo['future']) ? [] : json_decode($matchInfo['future'],true),
                'history'   =>  is_null($matchInfo['history']) ? [] : json_decode($matchInfo['history'],true),
            ];
            //队伍统计
            $teamStats = is_null($matchInfo['team_stats']) ? [] : json_decode($matchInfo['team_stats'],true);

            //集锦/录像
            $matchVedioModel = new \app\commonModel\MatchVedio();
            $video = $matchVedioModel->getByMatchId($id,0);

            //seo
            $seo = [];
            if (!empty($analysis['info'])){
                switch ($lujin){
                    case 'sqfx':
                        //【时间参数】西甲直播_【球队1】VS【球队2】赛前分析 - 网站名称
                        $seo['title'] = date('m月d日',$analysis['info']['match_time']) .$analysis['info']['competition_text'].'赛程表_'.
                            $analysis['info']['home_team_text'].'VS'.$analysis['info']['away_team_text'].self::INFO_INDEX_TITLE[$lujin].' - '.$this->webCommonTitle;
                        //【球队1】,【球队2】,【球队1】赛前分析,【球队2】赛前分析,【球队1】交锋历史,【球队2】交锋历史,【球队1】近期战绩,【球队2】近期战绩,【球队1】未来赛程,【球队2】未来赛程
                        $seo['keywords'] = $analysis['info']['home_team_text'].','.$analysis['info']['away_team_text'].','.
                            $analysis['info']['home_team_text'].'赛前分析,'.$analysis['info']['away_team_text'].'赛前分析,'.
                            $analysis['info']['home_team_text'].'交锋历史,'.$analysis['info']['away_team_text'].'交锋历史,'.
                            $analysis['info']['home_team_text'].'近期战绩,'.$analysis['info']['away_team_text'].'近期战绩,'.
                            $analysis['info']['home_team_text'].'未来赛程,'.$analysis['info']['away_team_text'].'未来赛程';
                        //【网站名称】提供【时间参数】西甲直播，【球队1】VS【球队2】赛前分析，包含【球队1】VS【球队2】交锋历史，主队近期战绩，以及【球队1】、【球队2】的主队未来赛程！
                        $seo['description'] = $this->webCommonTitle.'提供'.date('m月d日',$analysis['info']['match_time']).$analysis['info']['competition_text'].'直播,'.
                            $analysis['info']['home_team_text'].'VS'.$analysis['info']['away_team_text'].'赛前分析,包含'.
                            $analysis['info']['home_team_text'].'VS'.$analysis['info']['away_team_text'].'交锋历史,主队近期战绩,以及'.
                            $analysis['info']['home_team_text'].'、'.$analysis['info']['away_team_text'].'的主队未来赛程！';
                        break;
                    case 'zbxx':
                        //2023西甲直播_【时间参数】【球队1】VS【球队2】直播信息 - 网站名称
                        $seo['title'] = date('Y年',time()).$analysis['info']['competition_text'].'免费高清直播_'.date('m月d日',$analysis['info']['match_time']).
                            $analysis['info']['home_team_text'].'VS'.$analysis['info']['away_team_text'].'直播信息 - '.$this->webCommonTitle;
                        //关键词：【球队1】直播,【球队2】直播,【球队1】赛程,【球队2】赛程,【球队1】阵容,【球队2】阵容
                        $seo['keywords'] = $analysis['info']['home_team_text'].'直播,'.$analysis['info']['away_team_text'].'直播,'.
                            $analysis['info']['home_team_text'].'赛程,'.$analysis['info']['away_team_text'].'赛程,'.
                            $analysis['info']['home_team_text'].'阵容,'.$analysis['info']['away_team_text'].'阵容';
                        //描述：【调用赛事信息内容】
                        $seo['description'] = $this->webCommonTitle.'直播为您提供'.$analysis['info']['home_team_text'].'对阵'.$analysis['info']['away_team_text'].
                            '高清无插件直播地址,'.$analysis['info']['home_team_text'].'直播和'.$analysis['info']['away_team_text'].
                            '直播信号将在开赛前实时更新,刷新本页面或进入'.$this->webCommonTitle.'直播首页即可获取最新直播信号。喜欢看'.
                            $analysis['info']['home_team_text'].'VS'.$analysis['info']['away_team_text'].
                            '比赛的朋友可以提前收藏本页面以免错过您喜欢的赛事直播。';
                        break;
                    case 'jstj':
                        //【球队1】VS【球队2】阵容技术统计以及实时赛事数据 - 【网站名称】
                        $seo['title'] = $analysis['info']['home_team_text'].'VS'.$analysis['info']['away_team_text'].'阵容技术统计以及实时赛事数据 - '.$this->webCommonTitle;
                        //【球队1】技术分析,【球队2】技术分析,西甲赛事,【球队1】赛事数据,【球队2】赛事数据
                        $seo['keywords'] = $analysis['info']['home_team_text'].'技术分析,'.$analysis['info']['away_team_text'].'技术分析,'.
                            $analysis['info']['home_team_text'].'赛事数据,'.$analysis['info']['away_team_text'].'赛事数据,';
                        //【网站名称】是一个专为西甲足球迷打造的在线网站，提供最新的【球队1】VS【球队2】赛事实况数据。它提供有关每场比赛的实时赛事数据，包括赛事进度、比赛结果、技术统计、球员统计和赛事新闻，让你随时随地掌握西甲足球比赛的最新动态。
                        $seo['description'] = $this->webCommonTitle.'是一个专为西甲足球迷打造的在线网站，提供最新的'.
                            $analysis['info']['home_team_text'].'VS'.$analysis['info']['away_team_text'].
                            '赛事实况数据。它提供有关每场比赛的实时赛事数据，包括赛事进度、比赛结果、技术统计、球员统计和赛事新闻，让你随时随地掌握西甲足球比赛的最新动态。';
                        break;
                    case 'jjlx':
                        //【球队1】VS【球队2】赛事集锦和直播录像回放 - 【网站名称】
                        $seo['title'] = $analysis['info']['home_team_text'].'VS'.$analysis['info']['away_team_text'].'赛事集锦和直播录像回放 - '.$this->webCommonTitle;
                        //【球队1】赛事集锦,【球队2】赛事集锦,【球队1】高清直播,【球队2】高清直播,【球队1】录像回放,【球队2】录像回放
                        $seo['keywords'] = $analysis['info']['home_team_text'].'赛事集锦,'.$analysis['info']['away_team_text'].'赛事集锦,'.
                            $analysis['info']['home_team_text'].'高清直播,'.$analysis['info']['away_team_text'].'高清直播,'.
                            $analysis['info']['home_team_text'].'录像回放,'.$analysis['info']['away_team_text'].'录像回放';
                        //【网站名称】是一个专门为球迷提供西甲联赛比赛的实时直播的网站。本栏目提供【球队1】VS【球队2】赛事集锦和直播录像回放，包括的比赛，杯赛，友谊赛，甚至是青年联赛的比赛的直播和录像回放资料！
                        $seo['description'] = $this->webCommonTitle.'是一个专门为球迷提供西甲联赛比赛的实时直播的网站。本栏目提供'.
                            $analysis['info']['home_team_text'].'VS'.$analysis['info']['away_team_text'].'赛事集锦和直播录像回放，包括的比赛，杯赛，友谊赛，甚至是青年联赛的比赛的直播和录像回放资料！';
                        break;
                }
                View::assign('seo',$seo);
            }
            View::assign("analysis",$analysis);
            View::assign("teamStats",$teamStats);
            View::assign("video",$video);
            View::assign("matchLive",$matchLive);
            View::assign("id",$id);
        }

	    return view($temp);
	}
	
	public function teaminfo(){
        $param = get_params();
        $id = $param['id'] ?? 0;

        if ($id > 0){
            //队伍信息
            $teamModel = new \app\commonModel\FootballTeam();
            $data = $teamModel->getFootballTeamById($id);
            $cmpModel = new \app\commonModel\FootballCompetition();
            $cmpInfo = $cmpModel->info($data->competition_id);
            $data->competition_text = $cmpInfo->name_zh ?? '';
            $data->competition_short_text = $cmpInfo->short_name_zh ?? '';

            $model = new \app\commonModel\FootballMatch();
            $match = $model->getByTeam($id);

            //西甲【球队名称】球队简介_【球队名称】积分榜_【球队名称】赛程表 -【网站名称】
            $seo['title'] = $data->competition_short_text.$data->short_name_zh.'球队简介_'.
                $data->short_name_zh.'积分榜_'.
                $data->short_name_zh.'赛程表 - '.$this->webCommonTitle;
            //【球队名称】简介,【球队名称】积分榜,【球队名称】赛程表,【球队名称】赛事
            $seo['keywords'] = $data->short_name_zh.'简介,'.
                $data->short_name_zh.'积分榜,'.
                $data->short_name_zh.'赛程表,'.
                $data->short_name_zh.'赛事';
            //描述调用球队介绍
            $seo['description'] = $data->short_name_zh.'足球俱乐部（英文名：'.$data->name_en.'，粤语名：'.$data->name_zht.'，是'.
                $data->competition_text.'的一支足球俱乐部，当前'.$data->name_zh.'的市值为：'.$data->market_value.$data->market_value_currency.'，'.
                $data->name_zh.'成立时间是'.date('Y年',$data->foundation_time).'，'.$data->name_zh.'足球俱乐部总球员数为'.$data->total_players.'人，其中国家队球员数'.
                $data->national_players.'人，非本土球员'.$data->foreign_players.'人，我们将为你实时更新'.$data->name_zh.'赛事数据信息。';

            View::assign("seo",$seo);
            View::assign("team",$data);
            View::assign("match",$match);
        }

	    return view();
	}

    public function videolist(){
        $param = get_params();
        //赛程id
        $id = $param['id'] ?? 0;
        if ($id > 0){

            $footballMatchCountModel = new \app\commonModel\FootballMatchCount();
            //集锦/录像
            $video = $footballMatchCountModel->getMatchVideoCollection($id);

            $id = Env::get('Home.HOME_SPACE');
            $model = new \app\commonModel\FootballMatch();
            //联赛历史七天赛程
            $match = $model->getBeWeekData([$id]);

            preg_match_all('/(?<=\[)[^\]]+/', $video[0]['title'], $arrMatches);
            $title = str_replace($arrMatches[0],$this->webCommonTitle,$video[0]['title']);
            $seo = [
                'title' => $title,
                'keywords'=> '',
                'description'=>$video[0]['title']
            ];

            View::assign("video",$video);
            View::assign("match",$match);
            View::assign("seo",$seo);
        }

        return view();
    }


}
