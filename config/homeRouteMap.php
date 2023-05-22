<?php

// +----------------------------------------------------------------------
// | 前台路由映射设置
// +----------------------------------------------------------------------

return [
    'index' => [
        'route_name' => '首页',
        'route_path'    =>  '/'
    ],

    'live' => [
        'route_name' => '直播主页',
        'route_path'    =>  'live/'
    ],
    'live_zuqiu' => [
        'route_name' => '足球直播',
        'route_path'    =>  'live/zuqiu/'
    ],
    'live_zuqiu_detail' => [
        'route_name' => '足球直播详情',
        'route_path'    =>  'live/zuqiu/[:compname]/[:matchid]'
    ],
    'live_lanqiu' => [
        'route_name' => '篮球直播',
        'route_path'    =>  'live/lanqiu/'
    ],
    'live_lanqiu_detail' => [
        'route_name' => '篮球直播详情',
        'route_path'    =>  'live/lanqiu/[:compname]/[:matchid]'
    ],

    'luxiang' => [
        'route_name' => '录像主页',
        'route_path'    =>  'luxiang/'
    ],
    'luxiang_zuqiu' => [
        'route_name' => '足球录像',
        'route_path'    =>  'luxiang/zuqiu/'
    ],
    'luxiang_zuqiu_detail' => [
        'route_name' => '足球录像详情',
        'route_path'    =>  'luxiang/zuqiu/[:compname]/[:vid]'
    ],
    'luxiang_lanqiu' => [
        'route_name' => '篮球录像',
        'route_path'    =>  'luxiang/lanqiu/'
    ],
    'luxiang_lanqiu_detail' => [
        'route_name' => '篮球录像详情',
        'route_path'    =>  'luxiang/lanqiu/[:compname]/[:vid]'
    ],

    'jijin' => [
        'route_name' => '集锦主页',
        'route_path'    =>  'jijin/'
    ],
    'jijin_zuqiu' => [
        'route_name' => '足球集锦',
        'route_path'    =>  'jijin/zuqiu/'
    ],
    'jijin_zuqiu_detail' => [
        'route_name' => '足球集锦详情',
        'route_path'    =>  'jijin/zuqiu/[:compname]/[:vid]'
    ],
    'jijin_lanqiu' => [
        'route_name' => '篮球集锦',
        'route_path'    =>  'jijin/lanqiu/'
    ],
    'jijin_lanqiu_detail' => [
        'route_name' => '篮球集锦详情',
        'route_path'    =>  'jijin/lanqiu/[:compname]/[:vid]'
    ],

    'zixun_zuqiu' => [
        'route_name' => '足球资讯',
        'route_path'    =>  'zixun/zuqiu/'
    ],
    'zixun_zuqiu_detail' => [
        'route_name' => '足球资讯详情',
        'route_path'    =>  'zixun/zuqiu/[:compname]/[:aid]'
    ],
    'zixun_lanqiu' => [
        'route_name' => '篮球资讯',
        'route_path'    =>  'zixun/lanqiu/'
    ],
    'zixun_lanqiu_detail' => [
        'route_name' => '篮球资讯详情',
        'route_path'    =>  'zixun/lanqiu/[:compname]/[:aid]'
    ],

    'liansai_zuqiu' => [
        'route_name' => '足球联赛',
        'route_path'    =>  'liansai/zuqiu/'
    ],
    'liansai_zuqiu_detail' => [
        'route_name' => '足球联赛详情',
        'route_path'    =>  'liansai/zuqiu/[:compid]'
    ],
    'liansai_lanqiu' => [
        'route_name' => '篮球联赛',
        'route_path'    =>  'liansai/lanqiu/'
    ],
    'liansai_lanqiu_detail' => [
        'route_name' => '篮球联赛详情',
        'route_path'    =>  'liansai/lanqiu/[:compid]'
    ],

    'qiudui_zuqiu' => [
        'route_name' => '足球球队',
        'route_path'    =>  'qiudui/zuqiu/'
    ],
    'qiudui_zuqiu_detail' => [
        'route_name' => '足球球队详情',
        'route_path'    =>  'qiudui/zuqiu/[:teamid]'
    ],
    'qiudui_lanqiu' => [
        'route_name' => '篮球球队',
        'route_path'    =>  'qiudui/lanqiu/'
    ],
    'qiudui_lanqiu_detail' => [
        'route_name' => '篮球球队详情',
        'route_path'    =>  'qiudui/lanqiu/[:teamid]'
    ],
];
