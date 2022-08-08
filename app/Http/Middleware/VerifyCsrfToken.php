<?php

namespace App\Http\Middleware;
use Closure;
use Exception;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    public function handle($request, Closure $next)
    {
        try {
            $token = $request->session()->token();
            if($request->ajax() && $token!=$request->header('X-CSRF-Token')){
                return response()->json(['status' => 400,'messenger'=>'Authorization Token not found']);
            }
            if(!isset($token))
                return response()->json(['status' => 400,'messenger'=>'Authorization Token not found']);
        } catch (Exception $e) {
                return response()->json(['status' =>400,'messenger'=>'Authorization Token not found']);
        }
        return $next($request);
    }
}
