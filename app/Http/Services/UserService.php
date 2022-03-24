<?php

namespace App\Http\Services;

use App\Http\Repositories\UserRepository;
use App\Util\UtilJwt;
use App\Util\UtilResponse;
use App\Util\Validate;
use App\Util\UtilRandoms;
use Illuminate\Http\JsonResponse;
use Exception;

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

    /**
     * @throws Exception
     */
    public function register(string $phone, string $name, string $password) {
        try {
            if ($this->userRepository->isUserExist($phone)) {
                throw new Exception('User is existed');
            }else if (!$this->userRepository->createUser($name, $password, $phone)){
                throw new Exception("created user failed");
            }
        }catch (Exception $e){
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function login(string $phone, string $password): string {
        try {
            $userInfo = $this->userRepository->getUserInfo($phone);
            $this->userRepository->checkUsersAndPassword($phone, $password);
            return UtilJwt::getInstance()->encode(['usersId' => $userInfo->id]);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function getUsersInfo(int $id) {
        try {
            $dataInfo = $this->userRepository->getUserInfoById($id, true);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
        return $dataInfo;
    }

    public function refreshToken(int $id): JsonResponse {
        return UtilJwt::getInstance()->encode(['usersId' => $id]);
    }

    public function getUserTypeList(int $id): JsonResponse {
        if (empty($id)) {
            return UtilResponse::errorResponse("typeId error");
        }
        $data = $this->usersTypesMap[$id] ?? "";
        if ($data == "") {
            return UtilResponse::errorResponse("data error");
        }
        return UtilResponse::successResponse("success", $data);
    }

    public function resetPassword(string $account, string $email): Bool {
        $user = $this->userRepository->getUserInfoByAccount($account);

        if ($user->email == $email) {
            $code = UtilRandoms::randomString();
            \Log::info('password reset: '.$email.', '.$code);
            
            $this->userRepository->resetPassword($email, $code);
            return $this->sendMail($email, $code) ? true : false;
        } else {
            return false;
        }
    }

    private function sendMail(String $email, String $code): Bool {
        // $sender = new Mail();
        return true;
    }

}
