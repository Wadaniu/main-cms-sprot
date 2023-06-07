<?php

// +----------------------------------------------------------------------
// | 前台路由映射设置
// +----------------------------------------------------------------------

return [
    'index' => [
        'route_name'    => '首页',
        'route_path'    =>  '/',
        'keywords'      =>  []
    ],

    'live' => [
        'route_name'    => '直播',
        'route_path'    =>  '',
        'keywords'      =>  []
    ],
    'live_zuqiu' => [
        'route_name'    => '足球直播',
        'route_path'    =>  '/live-zuqiu/',
        'keywords'      =>  [
            'short_name_zh'   =>  '联赛简称'
        ]
    ],
    'live_zuqiu_detail' => [
        'route_name'    => '足球直播详情',
        'route_path'    =>  '/live-zuqiu/[:compname]/[:matchid]',
        'keywords'      =>  [
            'home_team_name'    =>  '主队简称',
            'away_team_name'    =>  '客队简称',
            'match_time'        =>  '比赛时间',
            'short_name_zh'     =>  '联赛简称'
        ]
    ],
    'live_lanqiu'       => [
        'route_name'    => '篮球直播',
        'route_path'    =>  '/live-lanqiu/',
        'keywords'      =>  [
            'short_name_zh'   =>  '联赛简称'
        ]
    ],
    'live_lanqiu_detail'=> [
        'route_name'    => '篮球直播详情',
        'route_path'    =>  '/live-lanqiu/[:compname]/[:matchid]',
        'keywords'      =>  [
            'home_team_name'    =>  '主队简称',
            'away_team_name'    =>  '客队简称',
            'match_time'        =>  '比赛时间',
            'short_name_zh'     =>  '联赛简称'
        ]
    ],

    'luxiang'           => [
        'route_name'    => '录像',
        'route_path'    =>  '',
        'keywords'      =>  []
    ],
    'luxiang_zuqiu'     => [
        'route_name'    => '足球录像',
        'route_path'    =>  '/luxiang-zuqiu/',
        'keywords'      =>  [
            'short_name_zh'   =>  '联赛简称'
        ]
    ],
    'luxiang_zuqiu_detail' => [
        'route_name'    => '足球录像详情',
        'route_path'    =>  '/luxiang-zuqiu/[:compname]/[:vid]',
        'keywords'      =>  [
            'title'   =>  '录像标题'
        ]
    ],
    'luxiang_lanqiu'    => [
        'route_name'    => '篮球录像',
        'route_path'    =>  '/luxiang-lanqiu/',
        'keywords'      =>  [
            'short_name_zh'   =>  '联赛简称'
        ]
    ],
    'luxiang_lanqiu_detail' => [
        'route_name'    => '篮球录像详情',
        'route_path'    =>  '/luxiang-lanqiu/[:compname]/[:vid]',
        'keywords'      =>  [
            'title'   =>  '录像标题'
        ]
    ],

    'jijin'             => [
        'route_name'    => '集锦',
        'route_path'    =>  '',
        'keywords'      =>  []
    ],
    'jijin_zuqiu'       => [
        'route_name'    => '足球集锦',
        'route_path'    =>  '/jijin-zuqiu/',
        'keywords'      =>  [
            'short_name_zh'   =>  '联赛简称'
        ]
    ],
    'jijin_zuqiu_detail'=> [
        'route_name'    => '足球集锦详情',
        'route_path'    =>  '/jijin-zuqiu/[:compname]/[:vid]',
        'keywords'      =>  [
            'title'   =>  '录像标题'
        ]
    ],
    'jijin_lanqiu'      => [
        'route_name'    => '篮球集锦',
        'route_path'    =>  '/jijin-lanqiu/',
        'keywords'      =>  [
            'short_name_zh'   =>  '联赛简称'
        ]
    ],
    'jijin_lanqiu_detail' => [
        'route_name'    => '篮球集锦详情',
        'route_path'    =>  '/jijin-lanqiu/[:compname]/[:vid]',
        'keywords'      =>  [
            'title'   =>  '录像标题'
        ]
    ],

    'zixun'           => [
        'route_name'    => '资讯',
        'route_path'    =>  '',
        'keywords'      =>  []
    ],
    'zixun_zuqiu'       => [
        'route_name'    => '足球资讯',
        'route_path'    =>  '/zixun-zuqiu/',
        'keywords'      =>  [
            'short_name_zh'   =>  '联赛简称'
        ]
    ],
    'zixun_zuqiu_detail'=> [
        'route_name'    => '足球资讯详情',
        'route_path'    =>  '/zixun-zuqiu/[:compname]/[:aid]',
        'keywords'      =>  [
            'title'     =>  '文章标题',
            'keyword'   =>  '文章关键字',
            'desc'      =>  '文章简介'
        ]
    ],
    'zixun_lanqiu'      => [
        'route_name'    => '篮球资讯',
        'route_path'    =>  '/zixun-lanqiu/',
        'keywords'      =>  [
            'short_name_zh'   =>  '联赛简称'
        ]
    ],
    'zixun_lanqiu_detail'=> [
        'route_name'    => '篮球资讯详情',
        'route_path'    =>  '/zixun-lanqiu/[:compname]/[:aid]',
        'keywords'      =>  [
            'title'     =>  '文章标题',
            'keyword'   =>  '文章关键字',
            'desc'      =>  '文章简介'
        ]
    ],

    'liansai'           => [
        'route_name'    => '联赛',
        'route_path'    =>  '',
        'keywords'      =>  []
    ],
    'liansai_zuqiu'     => [
        'route_name'    => '足球联赛',
        'route_path'    =>  '/liansai-zuqiu/',
        'keywords'      =>  []
    ],
    'liansai_zuqiu_detail' => [
        'route_name'    => '足球联赛详情',
        'route_path'    =>  '/liansai-zuqiu/[:compid]',
        'keywords'      =>  [
            'short_name_zh'   =>  '联赛简称'
        ]
    ],
    'liansai_lanqiu'    => [
        'route_name'    => '篮球联赛',
        'route_path'    =>  '/liansai-lanqiu/',
        'keywords'      =>  []
    ],
    'liansai_lanqiu_detail' => [
        'route_name'    => '篮球联赛详情',
        'route_path'    =>  '/liansai-lanqiu/[:compid]',
        'keywords'      =>  [
            'short_name_zh'   =>  '联赛简称'
        ]
    ],

    'qiudui'           => [
        'route_name'    => '球队',
        'route_path'    =>  '',
        'keywords'      =>  []
    ],
    'qiudui_zuqiu'      => [
        'route_name'    => '足球球队',
        'route_path'    =>  '/qiudui-zuqiu/',
        'keywords'      =>  []
    ],
    'qiudui_zuqiu_detail' => [
        'route_name'    => '足球球队详情',
        'route_path'    =>  '/qiudui-zuqiu/[:teamid]',
        'keywords'      =>  [
            'short_name_zh'   =>  '球队简称'
        ]
    ],
    'qiudui_lanqiu'     => [
        'route_name'    => '篮球球队',
        'route_path'    =>  '/qiudui-lanqiu/',
        'keywords'      =>  []
    ],
    'qiudui_lanqiu_detail' => [
        'route_name'    => '篮球球队详情',
        'route_path'    =>  '/qiudui-lanqiu/[:teamid]',
        'keywords'      =>  [
            'short_name_zh'   =>  '球队简称'
        ]
    ],
];
