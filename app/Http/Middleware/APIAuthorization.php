<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class APIAuthorization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header('token');
        if('I-am-secure-growth-lead-api' != base64_decode($token)) {
            return response()->json([
                'success' => false,
                'msg' => 'unauthorized access'
            ], 403);
        }
        return $next($request);
    }
}
