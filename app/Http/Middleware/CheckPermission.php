<?php

namespace App\Http\Middleware;
use App\LoginUserPermission;
use Closure;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $route)
    {

        $val = LoginUserPermission::hasPemrission($request->user_id, $request->company_id, $request->module_id, $route);
        if($val != $route)
        {return response()->json('not authorized');}
        return $next($request);
    }
}
