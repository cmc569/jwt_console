<?php

namespace App\Http\Controllers;

use App\Util\UtilResponse;

class AuthController extends Controller {
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('auth:api');
    }

    /**
     * @OA\Put(
     *     path="/auth/users/logout",
     *     tags={"使用者相關"},
     *     summary="使用者登出",
     *     description="",
     *     security={{"apiAuth":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="{'data':{},'msg':'succsess'}",
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="{'data':{},'msg':'error msg'}",
     *      )
     *     )
     */
    public function logout() {
        auth()->logout();
        return UtilResponse::successResponse("success");
    }

    /**
     * @OA\Put(
     *     path="/auth/users/refresh",
     *     tags={"使用者相關"},
     *     summary="使用者更新jwt",
     *     description="",
     *     security={{"apiAuth":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="{'data':{},'msg':'succsess'}",
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="{'data':{},'msg':'error msg'}",
     *      )
     *     )
     */
    public function refresh() {
        $data = [
            'access_token' => auth()->refresh(),
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => auth()->user()
        ];
        return UtilResponse::successResponse("success", $data);
    }


    /**
     * @OA\Get(
     *     path="/auth/users/user-info",
     *     tags={"使用者相關"},
     *     summary="使用者更新jwt",
     *     description="",
     *     security={{"apiAuth":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="{'data':{},'msg':'succsess'}",
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="{'data':{},'msg':'error msg'}",
     *      )
     * )
     */
    public function getUserInfo() {
        $data = ["dataInfo" => auth()->user()];
        return UtilResponse::successResponse("success", $data);
    }
}
