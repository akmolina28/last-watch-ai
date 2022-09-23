<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Auth;

use Closure;
use Illuminate\Support\Facades\Log;

class LocalIpMiddleware
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
        if (!Auth::check()) {
            $remote_addr = $_SERVER['REMOTE_ADDR'];

            if (str_starts_with($remote_addr, '192.168')) {
                $user_id = \App\User::first()->id;
                Auth::loginUsingId($user_id);
            }
        }
        return $next($request);
    }
}
