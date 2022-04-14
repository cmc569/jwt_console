<?php

namespace App\Http\Services;

use App\Http\Repositories\UserRepository;
use App\Util\UtilJwt;
use App\Util\UtilResponse;
use App\Util\Validate;
use App\Util\UtilRandoms;
use App\Http\Services\MailService;
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
    public function login(string $account, string $password): string {
        try {
            $userInfo = $this->userRepository->getUserInfo($account);
            $this->userRepository->checkUsersAndPassword($account, $password);
            // return UtilJwt::getInstance()->encode(['usersId' => $userInfo->id]);
            return UtilJwt::getInstance()->encode(['usersId' => $userInfo->code]);
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

    public function resetPassword(string $account, string $email)
    {
        $user = $this->userRepository->getUserInfoByAccount($account);

        if ($user->email == $email) {
            $code = UtilRandoms::randomString();
            \Log::info('password reset: '.$email.', '.$code);
            
            $this->userRepository->resetPassword($user->code, $email, $code);
            return $this->sendMail($email, $code) ? $code : false;
        } else {
            return false;
        }
    }

    private function sendMail(String $email, String $code)
    {
        $content = '您的驗證碼為：'.$code."\n\n此為系統自動發送，請勿回覆";

        \Log::info('Ask for reset password. ('.$email.', '.$content.')');
        return MailService::send($email, '帳號驗證碼', $content);
    }

    public function checkAuthCode(String $email, String $authCode)
    {
        $expired = 600;
        $user = $this->userRepository->checkAuthCode($email, $authCode);
        
        if (is_null($user)) {
            return null;
        } else {
            $created_at = strtotime($user->created_at);
            if (($created_at + $expired) < time()) {
                return 'expired';
            } else {
                $this->userRepository->deleteAuthCode($email, $authCode);
                // return UtilJwt::getInstance()->encode(['usersId' => $user->user_id], $expired);
                return UtilJwt::getInstance()->encode(['usersId' => $user->user_id], $expired);
            }
        }
    }

    public function updatePassword(Int $userId, String $password)
    {
        return $this->userRepository->updatePassword($userId, $password);
    }
}
