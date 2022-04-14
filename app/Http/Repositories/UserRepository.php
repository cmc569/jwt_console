<?php

namespace App\Http\Repositories;

use App\Crypto\Crypto;
use App\Http\Models\Users;
use App\Http\Models\UserPermission;
use App\Http\Models\ResetPassword;
use App\Util\UtilTime;
use Exception;

class UserRepository extends BaseRepository {

    /**
     * @throws Exception
     */
    public function createUser(string $name, string $password, string $phone): bool {
        try {
            $result = Users::create([
                'name' => $name,
                'phone' => $phone,
                'password' => Crypto::encode($password),
                'created_at' => UtilTime::timeNow(),
                'updated_at' => UtilTime::timeNow(),
            ]);
            
            return empty($result) ? false : true;
        }catch (Exception $e){
            // throw new Exception($e->getMessage());
            $time = time();
            \Log::error('Operation error. DB error('.$time.'):'.$e->getMessage());
            throw new Exception('Operation error('.$time.')');
        }
    }

    /**
     * @throws Exception
     */
    public function getUserInfo(string $account) {
        try {
            $dataInfo = Users::where('account', $account)->firstOrFail();
        }catch (Exception $e){
            // throw new Exception($e->getMessage());
            $time = time();
            \Log::error('Get User info Failed. DB error('.$time.'):'.$e->getMessage());
            throw new Exception('Get User info Failed('.$time.')');
        }
        return $dataInfo;
    }

    /**
     * @throws Exception
     */
    public function getUserInfoById(int $id, bool $permission=false) {
        try {
            $dataInfo = Users::where('id', $id);
            if ($permission) {
                $dataInfo = $dataInfo->with('permissions');
            }
            $dataInfo = $dataInfo->firstOrFail();
        }catch (Exception $e){
            // throw new Exception($e->getMessage());
            $time = time();
            \Log::error('Get User data Failed. DB error('.$time.'):'.$e->getMessage());
            throw new Exception('Get User data Failed('.$time.')');
        }
        return $dataInfo;
    }

    public function isUserExist(string $phone): bool {
        $count = Users::where('phone', $phone)
            ->orderBy('id')
            ->count();
        return $count > 0;
    }

    /**
     * @throws Exception
     */
    public function checkUsersAndPassword(string $account, string $password): bool {
        try {
            $userInfo = Users::where('account', $account)->firstOrFail();
            if (Crypto::decode($userInfo->password) != $password) throw new Exception("password is error");
        }catch (Exception $e){
            // throw new Exception($e->getMessage());
            $time = time();
            \Log::error('Login Failed. DB error('.$time.'):'.$e->getMessage());
            throw new Exception('Login Failed');
        }
        return true;
    }


    /**
     * @throws Exception
     */
    public function getUserInfoByAccount(string $account) {
        try {
            $dataInfo = Users::where('account', $account)->firstOrFail();
        }catch (Exception $e){
            // throw new Exception($e->getMessage());
            $time = time();
            \Log::error('Get Account info Failed. DB error('.$time.'):'.$e->getMessage());
            throw new Exception('Get Account info Failed('.$time.')');
        }
        return $dataInfo;
    }

    /**
     * 
     */
    public function resetPassword(Int $user_id, String $email, String $code) {
        try {
            ResetPassword::create([
                'user_id'   => $user_id,
                'code'      => $code,
                'email'     => $email,
            ]);
        } catch (Exception $e) {
            // throw new Exception($e->getMessage());
            $time = time();
            \Log::error('Reset password failed. DB error('.$time.'):'.$e->getMessage());
            throw new Exception('Reset password failed('.$time.')');
        }
    }

    public function checkAuthCode(String $email, String $code)
    {
        return ResetPassword::where('code', $code)->where('email', $email)->first();
    }

    public function deleteAuthCode(String $email, String $code)
    {
        return ResetPassword::where('code', $code)->where('email', $email)->delete();
    }

    public function updatePassword(Int $userId, String $password)
    {
        $user = Users::find($userId);
        $user->password = Crypto::encode($password);
        return $user->save();
    }
}
