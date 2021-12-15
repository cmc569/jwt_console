<?php

namespace App\Http\Controllers;

use App\Crypto\Crypto;
use App\Http\Services\UserService;
use App\Util\UtilResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller {
    private $userService;

    public function __construct(UserService $userService) {
        $this->userService = $userService;
    }

    /**
     * @OA\Post(
     *     path="/users/login",
     *     tags={"使用者相關"},
     *     summary="使用者登入",
     *     description="",
     *     @OA\RequestBody(
     *          required=true,
     *          description="Request Body Description",
     *          @OA\JsonContent(
     *          ref="#/components/schemas/DocsUsersLogin"
     *          )
     *      ),
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
    public function login(Request $request): JsonResponse {
        $phone = $request->get("phone") ?? "";
        $password = $request->get("password")  ?? "";
        return $this->userService->login($phone, $password);
    }

    /**
     * @OA\Post(
     *     path="/users/register",
     *     tags={"使用者相關"},
     *     summary="使用者註冊",
     *     description="",
     *     @OA\RequestBody(
     *          required=true,
     *          description="Request Body Description",
     *          @OA\JsonContent(
     *          ref="#/components/schemas/DocsUsersRegister"
     *          )
     *      ),
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
    public function register(Request $request): JsonResponse {
        $phone = $request->get("phone") ?? "";
        $name = $request->get("name") ?? "";
        $password = $request->get("password");
        return $this->userService->register($phone, $name, $password);
    }

    /**
     * @OA\Get(
     *     path="/users/user-type-list",
     *     tags={"使用者相關"},
     *     summary="取得使用者類型",
     *     description="",
     *     @OA\Parameter(
     *        name="typeId",
     *        in="query",
     *        description="typeId",
     *        required=true,
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="{'data':{},'msg':'succsess'}",
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="{'data':{},'msg':'error msg'}",
     *     )
     * )
     */
    public function getUserTypeList(Request $request): JsonResponse {
        $id = $request->query("typeId")  ?? 0;
        return $this->userService->getUserTypeList($id);
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
    public function refresh(Request $request): JsonResponse {
        $id = $request->get("usersId");
        return $this->userService->refreshToken($id);
    }


    /**
     * @OA\Get(
     *     path="/auth/users/user-info",
     *     tags={"使用者相關"},
     *     summary="取得使用者資訊",
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
    public function getUserInfo(Request $request): JsonResponse {
        $id = $request->get("usersId");
        return $this->userService->getUsersInfo($id);
    }
}
