<?php
namespace app\install\route;//命名空间路径
use think\facade\Route;//引用门面路由类

const CommonSpace = '\app\home\controller';

//首页
Route::get('/', 'index/index')->name('/');

//直播首页
Route::get('/live/', CommonSpace.'\live\Index@index')->name('/live/');
//直播足球
Route::get('/live/zuqiu/:compname?/:matchid?', CommonSpace.'\live\Zuqiu@index')->name('/live/zuqiu/');
//直播篮球
Route::get('/live/lanqiu/:compname?/:matchid?', CommonSpace.'\live\Lanqiu@index')->name('/live/lanqiu/');

//足球录像
Route::get('/luxiang/zuqiu/:page?/:compname?/:vid?', CommonSpace.'\luxiang\Zuqiu@index')->name('/luxiang/zuqiu/');
//篮球录像
Route::get('/luxiang/lanqiu/:page?/:compname?/:vid?', CommonSpace.'\luxiang\Lanqiu@index')->name('/luxiang/lanqiu/');
//录像首页
Route::get('/luxiang/:page?/', CommonSpace.'\luxiang\Index@index')->name('/luxiang/');

//足球集锦
Route::get('/jijin/zuqiu/:page?/:compname?/:vid?', CommonSpace.'\jijin\Zuqiu@index')->name('/jijin/zuqiu/');
//篮球集锦
Route::get('/jijin/lanqiu/:page?/:compname?/:vid?', CommonSpace.'\jijin\Lanqiu@index')->name('/jijin/lanqiu/');
//集锦主页
Route::get('/jijin/:page?/', CommonSpace.'\jijin\Index::index')->name('/jijin/');

//足球资讯
Route::get('/zixun/zuqiu/:page?/:compname?/:aid?', CommonSpace.'\zixun\Zuqiu@index')->name('/zixun/zuqiu/');
//篮球资讯
Route::get('/zixun/lanqiu/:page?/:compname?/:aid?', CommonSpace.'\zixun\Lanqiu@index')->name('/zixun/lanqiu/');
//资讯主页
Route::get('/zixun/:page?/', CommonSpace.'\zixun\Index::index')->name('/zixun/');

//足球联赛
Route::get('/liansai/zuqiu/:page?/:compid?', CommonSpace.'\liansai\Zuqiu@index')->name('/liansai/zuqiu/');
//篮球联赛
Route::get('/liansai/lanqiu/:page?/:compid?', CommonSpace.'\liansai\Lanqiu@index')->name('/liansai/lanqiu/');
//联赛首页
Route::get('/liansai/:page?/:keyword?', CommonSpace.'\liansai\Index@index')->name('/liansai/');

//足球球队
Route::get('/qiudui/zuqiu/:page?/:teamid?', CommonSpace.'\qiudui\Zuqiu@index')->name('/qiudui/zuqiu/');
//篮球球队
Route::get('/qiudui/lanqiu/:page?/:teamid?', CommonSpace.'\qiudui\Lanqiu@index')->name('/qiudui/lanqiu/');
//球队首页
Route::get('/qiudui/:page?/:keyword?', CommonSpace.'\qiudui\Index@index')->name('/qiudui/');
