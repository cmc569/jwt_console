<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\Route;
// use App\Http\Models\Users;

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

Route::get("/", function () {
    // $from   = \Carbon\Carbon::now()->startOfMonth();
    // $to     = \Carbon\Carbon::now()->endOfMonth();
    // return "from = {$from}, to = {$to}";

    // $user = Users::with('role')->find(1);
    // $user = Users::with('permissions')->find(1);
    // dd($user);
    // \Log::info('['.Route::currentRouteName().'] ');
});

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
        Route::post('/login', [UserController::class, 'login']);
        Route::post('/register', [UserController::class, 'register']);
        Route::get('/user-type-list', [UserController::class, 'getUserTypeList']);

        //重設密碼
        Route::post('/resend', [UserController::class, 'resend']);
    });
});

/* path: /api/v1/auth */
Route::group([
    'middleware' => ['cors', 'auth'],
    'prefix' => 'v1/auth'
], function () {
    Route::group(["prefix"=> "users"], function (){
        Route::put('/refresh', [UserController::class, 'refresh']);
        Route::get('/user-info', [UserController::class, 'getUserInfo']);
    });

    //客服
    Route::group(['middleware' => ['permission.hq', 'permission.market', 'permission.service']], function() {
        // Route::get('/user-info', [UserController::class, 'getUserInfo']);

        //行銷
        Route::group(['middleware' => ['permission.market']], function() {
            // Route::get('/user-info', [UserController::class, 'getUserInfo']);

            //總部
            Route::group(['middleware' => ['permission.hq']], function() {
                // Route::get('/user-info', [UserController::class, 'getUserInfo']);
            });
        });
    });

});
