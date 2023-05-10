<?php
namespace app\install\route;//命名空间路径
use think\facade\Route;//引用门面路由类
use think\facade\Env;
$route = array();

$route['index'] = Env::get('route.index');

$route['newadd_integral'] = Env::get('route.newadd_integral');
$route['newadd_news'] = Env::get('route.newadd_news');
$route['newsadd_newcont'] = Env::get('route.newsadd_newcont');
$route['newadd_playerdata'] = Env::get('route.newadd_playerdata');
$route['newadd_playback'] = Env::get('route.newadd_playback');

Route::get($route['index'], 'index/index')->name('/');


Route::get($route['index'].'/comp/:id', 'index/index')->name('comp');
Route::get($route['newadd_integral'] ,'newadd/integral')->name('integral');
Route::get($route['newadd_playerdata'].'/:name?','newadd/playerdata')->name('playerdata');
Route::get($route['newadd_news'].'/:page?/:label?' ,'newadd/news')->name('news');
Route::get($route['newadd_playback'].'/:id/:date?','newadd/playback')->name('playback');
Route::get($route['newsadd_newcont'].'/:id', 'newadd/newcont')->name('newcont');
Route::get($route['index'].'/info/:id', 'newadd/playinfo')->name('playinfo');
Route::get($route['index'].'/team/:id', 'newadd/teaminfo')->name('teaminfo');
Route::get($route['index'].'/video/:id', 'newadd/videolist')->name('videolist');