<?php

namespace App\Http\Services;

use App\Http\Repositories\UserRepository;
use App\Util\UtilJwt;
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

    public function register(string $phone, string $name, string $password): JsonResponse {
        if ($name == "") {
            return UtilResponse::errorResponse('Name error');
        } else if (!Validate::checkPhone($phone)) {
            return UtilResponse::errorResponse('Phone error');
        } else if (!Validate::checkPassword($password)) {
            return UtilResponse::toJson(false, 'Password format error', []);
        } else if ($this->userRepository->isUserExist($phone)) {
            return UtilResponse::errorResponse('User is existed');
        } else if ($this->userRepository->createUser($name, $password, $phone) != true) {
            return UtilResponse::errorResponse('Insert db error');
        } else {
            return UtilResponse::successResponse('success');
        }
    }

    public function login(string $phone, string $password): JsonResponse {
        if (!Validate::checkPhone($phone)) {
            return UtilResponse::errorResponse('Phone error');
        } else if (!Validate::checkPassword($password)) {
            return UtilResponse::errorResponse('Password format is error');
        } else if (!$this->userRepository->isUserExist($phone)) {
            return UtilResponse::errorResponse('User is not existed');
        } else if (!$this->userRepository->checkUsersAndPassword($phone, $password)) {
            return UtilResponse::errorResponse('password is error');
        } else {
            $userInfo = $this->userRepository->getUserInfo($phone);
            $token = UtilJwt::encode(['usersId' => $userInfo->id]);
            return UtilResponse::successResponse('success', $token);
        }
    }

    public function getUsersInfo(int $id): JsonResponse {
        if (empty($id)){
            return UtilResponse::errorResponse("typeId error");
        }
        $data = $this->userRepository->getUserInfoById($id);
        return UtilResponse::successResponse("success", $data);
    }

    public function refreshToken(int $id): JsonResponse {
        $userInfo = $this->userRepository->getUserInfoById($id);
        if ($userInfo->id == 0){
            return UtilResponse::errorResponse("id error");
        }
        $token = UtilJwt::encode(['usersId' => $userInfo->id]);
        return UtilResponse::successResponse("success", $token);
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
