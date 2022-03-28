<?php

namespace App\Http\Middleware;

use App\Util\UtilResponse;
use Closure;

class HeadQuarter
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->get("userInfo")->role_id <= 10) return $next($request);
        return UtilResponse::errorResponse("invalid access");
    }
}
