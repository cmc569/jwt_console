<?php

namespace App\Http\Repositories;

use App\Http\Models\Users;

class UserRepository extends BaseRepository {

    public function createUser(string $name, string $email, string $password): bool {
        $id = Users::insertGetId([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt($password)
        ]);
        return $id > 0;
    }

    public function isUserExist(string $email): bool {
        $count =  Users::where('email', $email)
            ->orderBy('id')
            ->count();
        return $count > 0;
    }

}
