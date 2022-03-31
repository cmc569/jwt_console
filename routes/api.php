<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Account\AccountController;
use App\Http\Controllers\Posts\PostsController;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\Route;
// use App\Http\Services\MailService;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::get("/", function () {
//     MailService::send("jiver@ms16.hinet.net", "SSSS", "AAA\n\nBBB", public_path("index.php"));
// });

if (!config('app.debug')){
    Route::get('docs', function (){
        abort(404);
    });
}
/* path: /api/v1 */
Route::group([
    'middleware' => ['cors'],
    'prefix' => 'v1'
], function () {
    Route::group(["prefix"=> "users"], function (){
        //登入
        Route::post('/login', [UserController::class, 'login']);
        
        //忘記密碼
        Route::post('/resend', [UserController::class, 'resend']);
        Route::post('/enterCode', [UserController::class, 'enterCode']);
    });
});

/* path: /api/v1/auth */
Route::group([
    'middleware' => ['cors', 'auth'],
    'prefix' => 'v1/auth'
], function () {
    Route::group(["prefix"=> "users"], function (){
        Route::put('/refresh', [UserController::class, 'refresh']);
    });

    //客服
    Route::group(['middleware' => ['permission.service']], function() {
        //會員管理

        //行銷
        Route::group(['middleware' => ['permission.market']], function() {
            //Dashboard
            
            //優惠卷管理

            //其他設定

            //總部
            Route::group(['middleware' => ['permission.hq']], function() {
                //權限管理
                Route::group(['prefix' => 'account'], function() {
                    Route::get('/',         [AccountController::class, 'index']);       //顯示所有帳號
                    Route::post('save',     [AccountController::class, 'save']);        //儲存新帳號
                    Route::post('edit',     [AccountController::class, 'edit']);        //顯示特定帳號
                    Route::put('update',    [AccountController::class, 'update']);      //更新帳號資訊
                    Route::delete('delete', [AccountController::class, 'delete']);      //刪除帳號
                });

                //其他設定
                Route::group(['prefix' => 'others'], function() {
                    Route::get('privacy',   [PostsController::class, 'privacy']);       //顯示隱私權文案設定
                    Route::put('privacy',   [PostsController::class, 'privacyUpdate']); //更新隱私權文案
                    Route::get('points',    [PostsController::class, 'points']);        //顯示紅利點數文案設定
                    Route::put('points',    [PostsController::class, 'pointsUpdate']);  //更新紅利點數文案
                    Route::get('values',    [PostsController::class, 'values']);        //顯示儲值金文案設定
                    Route::put('values',    [PostsController::class, 'valuesUpdate']);  //更新儲值金文案
                });
            });
        });
    });

});
