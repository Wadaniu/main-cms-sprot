<?php
namespace app\install\route;//命名空间路径
use think\facade\Route;//引用门面路由类

const CommonSpace = '\app\home\controller';

//首页
Route::get('/', 'index/index')->name('/');
//直播足球
Route::get('live/zuqiu/:id?', CommonSpace.'\live\Zuqiu@index')->name('live/zuqiu/');
//直播篮球
Route::get('live/lanqiu/:id?', CommonSpace.'\live\Lanqiu@index')->name('live/lanqiu/');
//足球录像
Route::get('luxiang/zuqiu/:id?', CommonSpace.'\luxiang\Zuqiu@index')->name('luxiang/zuqiu/');
//篮球录像
Route::get('luxiang/lanqiu/:id?', CommonSpace.'\luxiang\Lanqiu@index')->name('luxiang/lanqiu/');

//集锦主页
Route::get('jijin/', CommonSpace.'\jijin\Index::index')->name('jijin/');
//足球集锦
Route::get('jijin/zuqiu/[:comp_name]/[:match_id]', CommonSpace.'\jijin\Zuqiu::index')->name('jijin/zuqiu/');
//篮球集锦
Route::get('jijin/lanqiu/:id?', CommonSpace.'\jijin\Lanqiu@index')->name('jijin/lanqiu/');
//足球资讯
Route::get('zixun/zuqiu/:id?', CommonSpace.'\zixun\Zuqiu@index')->name('zixun/zuqiu/');
//篮球资讯
Route::get('zixun/lanqiu/:id?', CommonSpace.'\zixun\Lanqiu@index')->name('zixun/lanqiu/');
//足球联赛
Route::get('liansai/zuqiu/:id?', CommonSpace.'\liansai\Zuqiu@index')->name('liansai/zuqiu/');
//篮球联赛
Route::get('liansai/lanqiu/:id?', CommonSpace.'\liansai\Lanqiu@index')->name('liansai/lanqiu/');
//足球球队
Route::get('qiudui/zuqiu/:id?', CommonSpace.'\qiudui\Zuqiu@index')->name('qiudui/zuqiu/');
//篮球球队
Route::get('qiudui/lanqiu/:id?', CommonSpace.'\qiudui\Lanqiu@index')->name('qiudui/lanqiu/');
