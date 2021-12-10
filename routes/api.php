<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\Route;

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
if (!config('app.debug')){
    Route::get('docs', function (){
        abort(404);
    });
}
/* path: /api/v1 */
Route::group([
    'prefix' => 'v1'
], function () {
    Route::group(["prefix"=> "users"], function (){
        Route::post('/login', [UserController::class, 'login']);
        Route::post('/register', [UserController::class, 'register']);
        Route::get('/user-type-list', [UserController::class, 'getUserTypeList']);
    });
});

/* path: /api/auth/v1 */
Route::group([
    'middleware' => 'api',
    'prefix' => 'auth/v1'
], function () {
    Route::group(["prefix"=> "users"], function (){
        Route::put('/logout', [AuthController::class, 'logout']);
        Route::put('/refresh', [AuthController::class, 'refresh']);
        Route::get('/user-info', [AuthController::class, 'getUserInfo']);
    });
});
