<?php

namespace App\Http\Middleware;

use App\Util\UtilJwt;
use App\Util\UtilResponse;
use Closure;
use Illuminate\Http\Request;
use Exception;

class Authenticate {
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next) {
        try {
            $token = request()->bearerToken();
            $tokenDecode = UtilJwt::decode($token);
            $usersId = $tokenDecode["usersId"] ?? 0;
            if (empty($usersId)) throw new Exception("user is error");
            $request->attributes->set('usersId', $usersId);
            return $next($request);
        } catch (Exception $e) {
            return UtilResponse::errorResponse($e->getMessage());
        }
    }
}
