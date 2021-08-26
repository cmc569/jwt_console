<?php

namespace App\Http\Repositories;

use App\Http\Models\Users;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserRepository extends BaseRepository {

    public function createUser(string $name, string $email, string $password): bool {
        $id = DB::table('users')->insertGetId([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt($password)
        ]);
        return $id != 0;
    }

    public function isUserExist(string $email): bool {
        $count = DB::table('users')
            ->where("email", $email)
            ->count();
        return $count > 0;
    }

}
