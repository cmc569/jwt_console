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
            if (empty($token)) throw new Exception("token is error");
            $tokenDecode = UtilJwt::getInstance()->decode($token);
            $usersId = $tokenDecode["usersId"] ?? 0;
            if (empty($usersId)) throw new Exception("user is error");
            $userInfo = $this->userRepository->getUserInfoById($usersId);
            if ($userInfo->id == 0) throw new Exception("user is error");
            $request->attributes->set('usersId', $usersId);
            return $next($request);
        } catch (Exception $e) {
            return UtilResponse::errorResponse($e->getMessage());
        }
    }
}
