<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// use think\Facade;
// use think\Route;
use think\facade\Route;

// Route::rule('player/check','home/api_fish/test');
Route::get('test','home/index/test');
Route::post('jiemi','home/index/jiemi');
Route::post('/transaction/game/bet','/home/api_fish/bet');
Route::post('transaction/game/endround','home/api_fish/endround');
Route::post('transaction/game/rollout','home/api_fish/rollout');
Route::post('transaction/game/takeall','home/api_fish/takeall');
Route::post('transaction/game/rollin','home/api_fish/rollin');
Route::post('transaction/game/debit','home/api_fish/debit');
Route::post('transaction/game/credit','home/api_fish/credit');
Route::post('transaction/user/payoff','home/api_fish/payoff');
Route::post('transaction/game/refund','home/api_fish/refund');
Route::get('transaction/balance/:account','home/api_fish/balance');
Route::get('player/check/:account','home/api_fish/player');

//系统自动生成
//Route::get('lotteryCode','home/SysLotterCcode/lottery_code');
Route::get('judgeLottery','home/SysLotterCcode/judgeLottery');
Route::get('beforeJudgeLottery','home/SysLotterCcode/beforeJudgeLottery');
Route::post('SysCodePaiJiang','home/SysLotterCcode/paijiang');

Route::get('forExtension','djycpgk/Extension/forExtension');
Route::get('getCodeForExtension','djycpgk/Extension/getCodeForExtension');

// Route::miss('Error/index');
// Route::group('admin', function () {
		   
// 		    Route::rule('b', '/admin/index/childindex');
// 		    Route::miss('admin/miss');
// 		})->ext('html');

