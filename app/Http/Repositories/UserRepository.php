<?php

namespace App\Http\Repositories;

use App\Crypto\Crypto;
use App\Http\Models\Users;
use App\Util\UtilTime;
use phpseclib3\Crypt\Hash;

class UserRepository extends BaseRepository {

    public function createUser(string $name, string $password, string $phone): bool {
        $id = Users::insertGetId([
            'name' => $name,
            'phone' => $phone,
            'password' => Crypto::encode($password),
            'created_at' => UtilTime::timeNow(),
            'updated_at' => UtilTime::timeNow(),
        ]);
        return $id > 0;
    }

    public function getUserInfo(string $phone) {
        return Users::where('phone', $phone)->find(1);
    }

    public function getUserInfoById(int $id) {
        return Users::where('id', $id)->find(1);
    }

    public function isUserExist(string $phone): bool {
        $count = Users::where('phone', $phone)
            ->orderBy('id')
            ->count();
        return $count > 0;
    }

    public function checkUsersAndPassword(string $phone, string $password): bool {
        $userInfo = Users::where('phone', $phone)->find(1);
        return Crypto::decode($userInfo->password) == $password;
    }
}
