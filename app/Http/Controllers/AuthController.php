<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Validator;

class AuthController extends Controller {
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('auth:api');
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout() {
        auth()->logout();
        return response()->json(
            [
                "status" => true,
                "message" => "User successfully logout out",
                "data" => []
            ]
        );
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh() {
        response()->json(
            [
                "status" => true,
                "message" => "user's token refreshed successfully",
                "data" => [
                    'access_token' => auth()->refresh(),
                    'token_type' => 'bearer',
                    'expires_in' => auth()->factory()->getTTL() * 60,
                    'user' => auth()->user()
                ]
            ]
        );
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userProfile() {
        Log::info(auth()->user());
        return response()->json(auth()->user());
    }
}
