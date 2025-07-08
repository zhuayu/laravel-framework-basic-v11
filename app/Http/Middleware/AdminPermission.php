<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $permission = null)
    {
        if (Auth::guard('admin')->guest()) {
            return response('Unauthorized.', 401);
        }
        $permissionArr = explode(' ', $permission);
        $hasPermission = Auth::user()->hasPermission($permissionArr);
        if(!$hasPermission) {
            return response('Unauthorized.', 403);
        }

        return $next($request);
    }
}
