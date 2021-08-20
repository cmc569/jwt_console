<?php

namespace App\Http\Controllers;

use App\Crypto\Crypto;
use App\Http\Models\User;
use App\Util\Validate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Validator;
use App\Util\UtilResponse;

class UserController extends Controller {

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request) {
        $parameter = $request->all();
        $data = [
            "email" => Crypto::decode($parameter["email"]),
            "password" => Crypto::decode($parameter["password"]),
        ];
        if (!Validate::CheckEmail($data["email"])) {
            return UtilResponse::toJson(false, 'Email format is error', []);
        } else if (!Validate::CheckPassword($data["password"])) {
            return UtilResponse::toJson(false, 'Password format is  error', []);
        } else if (!User::isPasswordAuth($data["email"], $data["password"])) {
            return UtilResponse::toJson(false, 'Password is not correct', []);
        } else if (!$token = auth()->attempt($data)) {
            return UtilResponse::toJson(false, 'Unauthorized', []);
        } else {
            return UtilResponse::toJson(true, 'Login successfully', [
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth()->factory()->getTTL() * 60,
                'user' => auth()->user()
            ]);
        }
    }

    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request) {
        $parameter = $request->all();
        Log::debug("debug", $request->all());
        $data = [
            "name" => Crypto::decode($parameter["name"]),
            "email" => Crypto::decode($parameter["email"]),
            "password" => Crypto::decode($parameter["password"]),
        ];
        if (!Validate::CheckEmail($data["email"])) {
            return UtilResponse::toJson(false, 'Email error', []);
        } else if ($data["email"] == "") {
            return UtilResponse::toJson(false, 'Name error', []);
        } else if (!Validate::CheckPassword($data["password"])) {
            return UtilResponse::toJson(false, 'Password format error', []);
        } else if (User::isUserExist($data["email"])) {
            return UtilResponse::toJson(false, 'User is existed', []);
        } else if (User::addUserInfo($data["name"], $data["email"], $data["password"]) != true) {
            return UtilResponse::toJson(false, 'Insert db error', []);
        } else {
            return UtilResponse::toJson(true, 'User successfully registered', []);
        }
    }
}
