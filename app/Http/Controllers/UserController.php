<?php

namespace App\Http\Controllers;

use App\Crypto\Crypto;
use App\Http\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller {
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

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
        return $this->userService->login($data["email"], $data["password"]);
    }

    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request) {
        $parameter = $request->all();
        $data = [
            "name" => Crypto::decode($parameter["name"]),
            "email" => Crypto::decode($parameter["email"]),
            "password" => Crypto::decode($parameter["password"]),
        ];
        return $this->userService->register($data["name"], $data["email"], $data["password"]);
    }
}
