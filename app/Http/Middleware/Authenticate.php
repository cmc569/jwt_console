<?php
namespace App\Http\Middleware;
use App\Util\UtilResponse;
use Closure;
use JWTAuth;
use Exception;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;


class Authenticate extends BaseMiddleware
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
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (Exception $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){
                return UtilResponse::toJson(false, 'Token is Invalid');
            }else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException){
                return UtilResponse::toJson(false, 'Token is Expired');
            }else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenBlacklistedException){
                return UtilResponse::toJson(false, 'Token is Blacklisted');
            }else{
                return UtilResponse::toJson(false, 'Authorization Token not found');
            }
        }
        return $next($request);
    }
}
