<?php

namespace App\Http\Controllers;

use App\Crypto\Crypto;
use App\Http\Services\UserService;
use App\Util\UtilResponse;
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
    public function login(Request $request) {
        $email = $request->get("email") ?? "";
        $password = $request->get("password")  ?? "";
        return $this->userService->login($email, $password);
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
    public function Register(Request $request) {
        $email = $request->get("email") ?? "";
        $name = $request->get("name") ?? "";
        $password = $request->get("password");
        return $this->userService->register($name, $email, $password);
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
    public function getUserTypeList(Request $request) {
        $id = $request->query("typeId")  ?? 0;
        return $this->userService->getUserTypeList($id);
    }
}
