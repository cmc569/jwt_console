<?php

namespace App\Http\Middleware;

use App\Http\Repositories\UserRepository;
use App\Util\UtilJwt;
use App\Util\UtilResponse;
use Closure;
use Illuminate\Http\Request;
use Exception;

class Authenticate {
    private $userRepository;

    function __construct(userRepository $userRepository) {
        $this->userRepository = $userRepository;
    }
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
            if (empty($token)) return UtilResponse::errorResponse("token is error");

            $tokenDecode = UtilJwt::getInstance()->decode($token);
            if ($tokenDecode['exp'] < time()) return UtilResponse::errorResponse("token expired");

            $usersId = $tokenDecode["usersId"] ?? null;
            if (empty($usersId)) return UtilResponse::errorResponse("user is error");

            // $userInfo = $this->userRepository->getUserInfoById($usersId);
            $userInfo = $this->userRepository->getUserInfoByCode($usersId);
            if ($userInfo->id == 0) return UtilResponse::errorResponse("user is error");

            // $request->attributes->set('usersId', $usersId);
            $request->attributes->set('usersId', $userInfo->id);
            $request->attributes->set('userInfo', $userInfo);
            // $request->attributes->set('userPermission', $userInfo->permissions);
            return $next($request);
        } catch (Exception $e) {
            return UtilResponse::errorResponse($e->getMessage());
        }
    }
}
