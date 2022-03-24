<?php

namespace App\Http\Middleware;

use App\Http\Repositories\UserRepository;

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
        // $userPermission = $this->userRepository->getUserPermissionById($request->get("usersId"));
        return $next($request);


    }
}
