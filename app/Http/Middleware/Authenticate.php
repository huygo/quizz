<?php

namespace App\Http\Middleware;
use Closure;
use Exception;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    public function handle($request, Closure $next)
    {
        if ($request->session()->exists('ACCOUNT_LOGIN')) {
            return $next($request);
        }else{
            return redirect()->route('login');
        }
    }
}
