<?php

namespace App\Http\Services;

use App\Http\Repositories\UserRepository;
use App\Util\UtilResponse;
use App\Util\Validate;
use Illuminate\Http\JsonResponse;

class UserService {
    private $userRepository;

    public function __construct(UserRepository $userRepository) {
        $this->userRepository = $userRepository;
    }

    public function register(string $name, string $email, string $password): JsonResponse {
        if (!Validate::CheckEmail($email)) {
            return UtilResponse::toJson(false, 'Email error', []);
        } else if ($name == "") {
            return UtilResponse::toJson(false, 'Name error', []);
        } else if (!Validate::CheckPassword($password)) {
            return UtilResponse::toJson(false, 'Password format error', []);
        } else if ($this->userRepository->isUserExist($email)) {
            return UtilResponse::toJson(false, 'User is existed', []);
        } else if ($this->userRepository->createUser($name, $email, $password) > 0) {
            return UtilResponse::toJson(true, 'User successfully registered', []);
        } else {
            return UtilResponse::toJson(false, 'Insert db error', []);
        }
    }

    public function login(string $email, string $password): JsonResponse {
        if (!Validate::CheckEmail($email)) {
            return UtilResponse::toJson(false, 'Email format is error', []);
        } else if (!Validate::CheckPassword($password)) {
            return UtilResponse::toJson(false, 'Password format is error', []);
        } else if (!$token = auth()->attempt(["email" => $email, "password" => $password])) {
            return UtilResponse::toJson(false, 'Unauthorized', []);
        } else if (!$this->userRepository->isUserExist($email)) {
            return UtilResponse::toJson(false, 'User is not existed', []);
        } else {
            return UtilResponse::toJson(true, 'Login successfully', [
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth()->factory()->getTTL() * 60,
                'user' => auth()->user()
            ]);
        }
    }

}
