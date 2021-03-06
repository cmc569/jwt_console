<?php

namespace App\Http\Middleware;

use App\Http\Repositories\UserRepository;
use App\Util\UtilResponse;
use Closure;

class Permissions
{
    private $userRepository;

    function __construct(userRepository $userRepository) {
        $this->userRepository = $userRepository;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        dd($request->get("userPermission"));
        if ($request->get("userPermission")->where('id', 1)->isEmpty()) 
            return UtilResponse::errorResponse("invalid access");

        return $next($request);
    }
}
