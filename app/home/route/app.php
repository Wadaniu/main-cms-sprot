<?php
namespace app\install\route;//命名空间路径
use think\facade\Route;//引用门面路由类

const CommonSpace = '\app\home\controller';

//首页
Route::get('/', 'index/index')->name('/');

//直播足球
Route::get('/live-zuqiu/:compname?/:matchid?', CommonSpace.'\live\Zuqiu@index')->name('/live-zuqiu/');
//直播篮球
Route::get('/live-lanqiu/:compname?/:matchid?', CommonSpace.'\live\Lanqiu@index')->name('/live-lanqiu/');

//足球录像
Route::get('/luxiang-zuqiu/:compname?/:vid?', CommonSpace.'\luxiang\Zuqiu@index')->name('/luxiang-zuqiu/');
//篮球录像
Route::get('/luxiang-lanqiu/:compname?/:vid?', CommonSpace.'\luxiang\Lanqiu@index')->name('/luxiang-lanqiu/');

//足球集锦
Route::get('/jijin-zuqiu/:compname?/:vid?', CommonSpace.'\jijin\Zuqiu@index')->name('/jijin-zuqiu/');
//篮球集锦
Route::get('/jijin-lanqiu/:compname?/:vid?', CommonSpace.'\jijin\Lanqiu@index')->name('/jijin-lanqiu/');

//足球资讯
Route::get('/zixun-zuqiu/:aid?', CommonSpace.'\zixun\Zuqiu@index')->name('/zixun-zuqiu/');
//篮球资讯
Route::get('/zixun-lanqiu/:aid?', CommonSpace.'\zixun\Lanqiu@index')->name('/zixun-lanqiu/');
//资讯总页
Route::get('/search/:page?/:keywords_id?', CommonSpace.'\zixun\Index@index')->name('/search/');

//足球联赛
Route::get('/liansai-zuqiu/:compid?', CommonSpace.'\liansai\Zuqiu@index')->name('/liansai-zuqiu/');
//篮球联赛
Route::get('/liansai-lanqiu/:compid?', CommonSpace.'\liansai\Lanqiu@index')->name('/liansai-lanqiu/');
//联赛总页
Route::get('/liansai-search/:page?/:keyword?', CommonSpace.'\liansai\Index@index')->name('/liansai-search/');

//足球球队
Route::get('/qiudui-zuqiu/:teamid?', CommonSpace.'\qiudui\Zuqiu@index')->name('/qiudui-zuqiu/');
//篮球球队
Route::get('/qiudui-lanqiu/:teamid?', CommonSpace.'\qiudui\Lanqiu@index')->name('/qiudui-lanqiu/');
//球队总页
Route::get('/qiudui-search/:page?/:keyword?', CommonSpace.'\qiudui\Index@index')->name('/qiudui-search/');

//篮球积分榜
Route::get('/jifen-lanqiu/:compname?', CommonSpace.'\jifen\Lanqiu@index')->name('/jifen-lanqiu/');
//足球积分榜
Route::get('/jifen-zuqiu/:compname?', CommonSpace.'\jifen\Zuqiu@index')->name('/jifen-zuqiu/');

//Route::get('/:id?', 'index/index')->name('/');
Route::miss(function () {
    throw new \think\exception\HttpException(404, '找不到页面');
});