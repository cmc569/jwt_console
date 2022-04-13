<?php

namespace App\Http\Controllers;

use Exception;
use App\Http\Services\UserService;
use App\Util\UtilResponse;
use App\Util\Validate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
        $validator = Validator::make($request->input(), [
            'account'   => ['required', 'string'],
            'password'  => ['required', 'string'],
        ]);
 
        if ($validator->fails()) {
            return UtilResponse::errorResponse("invalid paramaters");
        }

        try {
            $token = $this->userService->login($request->input("account"), $request->input("password"));
            return UtilResponse::successResponse("success", $token);
        } catch (Exception $e) {
            return UtilResponse::errorResponse($e->getMessage());
        }

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
        $validator = Validator::make($request->input(), [
            'name'      => ['required', 'string'],
            'email'     => ['required', 'email:rfc,dns'],
            'account'   => ['required', 'string'],
            'role'      => ['required', Rule::in(['總部', '行銷', '客服'])],
            'password'  => ['required', 'string'],
        ]);
 
        if ($validator->fails()) {
            return UtilResponse::errorResponse("invalid paramaters");
        }

        try {
            $this->userService->register($request->input());
            return UtilResponse::successResponse("success");
        } catch (Exception $e) {
            return UtilResponse::errorResponse($e->getMessage());
        }

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
        $id = $request->query("typeId") ?? 0;
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
        return UtilResponse::successResponse("success", $this->userService->refreshToken($id));
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
        try {
            $id = $request->get("usersId") ?? 0;
            $dataInfo = $this->userService->getUsersInfo($id);
            return UtilResponse::successResponse("success", $dataInfo);
        } catch (\Exception $e) {
            return UtilResponse::errorResponse($e->getMessage());
        }
    }


    /**
     * @OA\Get(
     *     path="/users/user-info",
     *     tags={"使用者相關"},
     *     summary="要求重設密碼",
     *     description="",
     *     @OA\RequestBody(
     *          required=true,
     *          description="Request Body Description",
     *          @OA\JsonContent(
     *          ref="#/components/schemas/DocsUsersResend"
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
     * )
     */
    public function resend(Request $request): JsonResponse
    {
        $validator = Validator::make($request->input(), [
            'account'   => ['required', 'string'],
            'email'     => ['required', 'email:rfc,dns'],
        ]);
 
        if ($validator->fails()) {
            return UtilResponse::errorResponse("invalid paramaters");
        }

        if ($code = $this->userService->resetPassword($request->input('account'), $request->input('email'))) {
            return UtilResponse::successResponse("success", $code);
        } else {
            return UtilResponse::errorResponse("mail send failed");
        }
    }

    public function enterCode(Request $request)
    {
        $validator = Validator::make($request->input(), [
            'email'     => ['required', 'email:rfc,dns'],
            'authCode'  => ['required', 'string'],
        ]);
 
        if ($validator->fails()) {
            return UtilResponse::errorResponse("invalid paramaters");
        }

        if ($data = $this->userService->checkAuthCode($request->input('email'), $request->input('authCode'))) {
            if (is_null($data)) {
                return UtilResponse::errorResponse("user not found");
            } else if ($data == 'expired') {
                return UtilResponse::errorResponse("auth code expired");
            }

            return UtilResponse::successResponse("success", $data);
        } else {
            return UtilResponse::errorResponse("invalid enter code");
        }

    }

    public function reset()
    {
        $validator = Validator::make($request->input(), [
            'password'  => ['required', 'confirmed', 'regex:/^(?=.*[a-zA-Z])(?=.*[0-9]).{6,12}$/'],
        ]);
 
        if ($validator->fails()) {
            return UtilResponse::errorResponse("invalid password");
        }

        if ($this->userService->updatePassword($request->get('usersId'), $request->input('password'))) {
            return UtilResponse::successResponse("success");
        } else {
            return UtilResponse::errorResponse("failed");
        }

    }
}
