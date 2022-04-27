<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Accounts\AccountsController;
use App\Http\Controllers\Posts\PostsController;
use App\Http\Controllers\Members\MembersController;
use App\Http\Controllers\Members\TransTicketController;
use App\Http\Controllers\Members\GivePointsController;
use App\Http\Controllers\Members\StoreValueController;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\Route;
// use App\Http\Services\MailService;
// use App\Util\AccunixLineApi;
use App\Http\Controllers\Members\CouponController;
use Illuminate\Http\Request;

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
    // MailService::send("jiver@ms16.hinet.net", "SSSS", "AAA\n\nBBB", public_path("index.php"));


    // $accunix = new AccunixLineApi(env('ACCUNIX_LINE_BOT_ID'));
    // $accunix->setAccessToken(env('ACCUNIX_API_TOKEN'));
    // dd($accunix->couponGift('U6670ce431ab50a1655549921f88407ad', '180410df6f754Y'));
    // dd($accunix->couponGift('U45aa5267979d6d018f407b4b2112e372', '180410df6f754Y'));

    // dd($accunix->couponGift('U6670ce431ab50a1655549921f88407ad', '180410f63das4y'));

// });

// if (!config('app.debug')){
//     Route::get('docs', function (){
//         abort(404);
//     });
// }

Route::get('/test',function(){
    //拖曳yapi產生的swaggerApi.json 產生Authorizationc跟https選項
    return view('index');
});

Route::post('/upload/test',function(Request $request){

    echo $_POST['path']."/".$_FILES['csv_file']['name'];
    file_put_contents($_POST['path']."/".$_FILES['csv_file']['name'], file_get_contents($_FILES['csv_file']['tmp_name']));
    echo 'OK';exit;
    if ($request->hasFile('csv_file')) {
        $fileName = $request->file('csv_file')->getClientOriginalName();
        $path = $request->path;
        $request->file('csv_file')->move($path, $fileName);
    }
});

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
        Route::put('/refresh',      [UserController::class, 'refresh']);
        Route::get('/user-info',    [UserController::class, 'getUserInfo']);
        Route::patch('/reset',      [UserController::class, 'reset']);
    });

    //客服
    Route::group(['middleware' => ['permission.service']], function() {
        //會員管理
        Route::group(['prefix' => 'member'], function() {
            // Route::get('/',              [MembersController::class, 'index']);           //會員列表
            Route::post('/getMembers',      [MembersController::class, 'getMembers']);      //會員資料列表
            Route::post('/csv',             [MembersController::class, 'csv']);             //會員csv下載
            Route::post('/member',          [MembersController::class, 'member']);          //會員基本資料
            Route::patch('/memberName',     [MembersController::class, 'memberName']);      //更新會員姓名
            Route::patch('/memberEmail',    [MembersController::class, 'memberEmail']);     //更新會員email
            Route::patch('/memberBirthday', [MembersController::class, 'memberBirthday']);  //更新會員生日
            Route::post('/memberAccount',   [MembersController::class, 'memberAccount']);   //會員帳戶總覽

            Route::post('/orderList',   [MembersController::class, 'orderList']);       //會員交易清單
            Route::post('/orderDetail', [MembersController::class, 'orderDetail']);     //會員交易明細

            Route::post('/transTicket', [TransTicketController::class, 'list']);        //查看轉贈票券

            Route::get('/givePointUploadList',  [GivePointsController::class, 'index']);        //點數發送中心列表
            Route::post('/givePointUpload',     [GivePointsController::class, 'messUploads']);  //點數發送中心上傳檔案
            Route::delete('/givePointUpload',   [GivePointsController::class, 'messDelete']);   //點數發送中心刪除

            Route::post('/givePoint',     [GivePointsController::class, 'givePoint']);      //手動調動紅利點數

            Route::post('/storedValuePlus', [StoreValueController::class, 'plus']);
            Route::post('/cancelValuePlus', [StoreValueController::class, 'minus']);
            Route::post('/getOrderList', [StoreValueController::class, 'orderList']);

            Route::post('/getCampaignList', [CouponController::class, 'campaignList']);
            Route::post('/getCouponList', [CouponController::class, 'couponList']);
            Route::post('/sendCoupon', [CouponController::class, 'couponGift']);
            Route::post('/couponVerify', [CouponController::class, 'couponVerify']);
            Route::post('/couponUnverify', [CouponController::class, 'unverify']);
        });

        //行銷
        Route::group(['middleware' => ['permission.market']], function() {
            //Dashboard


            //優惠卷管理


            //其他設定
            Route::group(['prefix' => 'others'], function () {
                Route::get('privacy',   [PostsController::class, 'privacy']);       //顯示隱私權文案設定
                Route::put('privacy',   [PostsController::class, 'privacyUpdate']); //更新隱私權文案
                Route::get('points',    [PostsController::class, 'points']);        //顯示紅利點數文案設定
                Route::put('points',    [PostsController::class, 'pointsUpdate']);  //更新紅利點數文案
                Route::get('values',    [PostsController::class, 'values']);        //顯示儲值金文案設定
                Route::put('values',    [PostsController::class, 'valuesUpdate']);  //更新儲值金文案
            });

            //總部
            Route::group(['middleware' => ['permission.hq']], function() {
                //權限管理
                Route::group(['prefix' => 'account'], function() {
                    Route::post('/',        [AccountsController::class, 'index']);       //顯示所有帳號
                    Route::post('save',     [AccountsController::class, 'save']);        //儲存新帳號
                    Route::post('edit',     [AccountsController::class, 'edit']);        //顯示特定帳號
                    Route::put('update',    [AccountsController::class, 'update']);      //更新帳號資訊
                    Route::delete('delete', [AccountsController::class, 'delete']);      //刪除帳號
                });
            });

        });
    });

});
