<?php

namespace App\Http\Services;

use App\Http\Repositories\UserRepository;
use App\Util\UtilResponse;
use App\Util\Validate;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class UserService {
    private $userRepository;
    private $usersTypesMap = [
        1 => "一般",
        2 => "Vip",
        3 => "Vvip",
    ];

    public function __construct(UserRepository $userRepository) {
        $this->userRepository = $userRepository;
    }

    public function register(string $name, string $email, string $password): JsonResponse {
        if (!Validate::checkEmail($email)) {
            return UtilResponse::errorResponse('Email error');
        } else if ($name == "") {
            return UtilResponse::errorResponse('Name error');
        } else if (!Validate::checkPassword($password)) {
            return UtilResponse::toJson(false, 'Password format error', []);
        } else if ($this->userRepository->isUserExist($email)) {
            return UtilResponse::errorResponse('User is existed');
        } else if ($this->userRepository->createUser($name, $email, $password) != true) {
            return UtilResponse::errorResponse('Insert db error');
        } else {
            return UtilResponse::successResponse('success');
        }
    }

    public function login(string $email, string $password): JsonResponse {
        if (!Validate::checkEmail($email)) {
            return UtilResponse::errorResponse('Email format is error');
        } else if (!Validate::checkPassword($password)) {
            return UtilResponse::errorResponse('Password format is error');
        } else if (!$token = auth()->attempt(["email" => $email, "password" => $password])) {
            return UtilResponse::errorResponse('Unauthorized');
        } else if (!$this->userRepository->isUserExist($email)) {
            return UtilResponse::errorResponse('User is not existed');
        } else {
            return UtilResponse::successResponse('success', [
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth()->factory()->getTTL() * 60,
                'user' => auth()->user()
            ]);
        }
    }

    public function getUserTypeList(int $id): JsonResponse {
        if (empty($id)){
            return UtilResponse::errorResponse("typeId error");
        }
        $data = $this->usersTypesMap[$id] ?? "";
        if ($data == ""){
            return UtilResponse::errorResponse("data error");
        }
        return UtilResponse::successResponse("success", $data);
    }


}
