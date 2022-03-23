<?php

namespace App\Http\Repositories;

use App\Crypto\Crypto;
use App\Http\Models\Users;
use App\Http\Models\UserPermission;
use App\Util\UtilTime;
use Exception;

class UserRepository extends BaseRepository {

    /**
     * @throws Exception
     */
    public function createUser(string $name, string $password, string $phone): bool {
        // dd($name, Crypto::encode($password), $phone);
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
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function getUserInfo(string $phone) {
        try {
            $dataInfo = Users::where('phone', $phone)->firstOrFail();
        }catch (Exception $e){
            throw new Exception($e->getMessage());
        }
        return $dataInfo;
    }

    /**
     * @throws Exception
     */
    public function getUserInfoById(int $id) {
        try {
            $dataInfo = Users::where('id', $id)->firstOrFail();
        }catch (Exception $e){
            throw new Exception($e->getMessage());
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
    public function checkUsersAndPassword(string $phone, string $password): bool {
        try {
            $userInfo = Users::where('phone', $phone)->firstOrFail();
            if (Crypto::decode($userInfo->password) != $password) throw new Exception("password is error");
        }catch (Exception $e){
            throw new Exception($e->getMessage());
        }
        return true;
    }

    /**
     * 
     */
    public function getUserPermissionById(int $id) {
        $permissions = UserPermission::where('user_id', $id)->get();
    }
}
