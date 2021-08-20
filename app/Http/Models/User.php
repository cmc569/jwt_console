<?php

namespace App\Http\Models;

use App\Crypto\Crypto;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Contracts\JWTSubject;


class User extends Authenticatable implements JWTSubject {
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier() {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims() {
        return [];
    }

    public static function addUserInfo($name, $email, $password): bool {
        $id = DB::table('users')->insertGetId([
            'name'              => $name,
            'email'             => $email,
            'password'          => bcrypt($password)
        ]);
        return $id != 0;
    }
    public static function isUserExist($email): bool {
        $count = DB::table('users')
            ->where("email", $email)
            ->count();
        return $count > 0;
    }
    public static function isPasswordAuth($email, $password): bool {
        $data = DB::table('users')
            ->where('email', $email)
            ->first();
        $dbPassword = $data->password;
        return Hash::check($password, $dbPassword);
    }
}
