<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Laravel\Sanctum\PersonalAccessToken;

class ApiTokenAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();
        
        if (!$token) {
            $token = $request->header('X-API-Token');
        }
        
        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'API token required. Provide via Authorization: Bearer header or X-API-Token header.'
            ], 401);
        }

        // Find Sanctum token (format: id|tokenhash)
        $accessToken = PersonalAccessToken::findToken($token);
        
        if (!$accessToken) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid API token'
            ], 401);
        }

        // Check if token is valid
        if (!$accessToken->tokenable) {
            return response()->json([
                'success' => false,
                'message' => 'Token user not found'
            ], 401);
        }

        // Update last used timestamp
        $accessToken->forceFill(['last_used_at' => now()])->save();
        
        // Set the authenticated user on the request
        $request->setUserResolver(function () use ($accessToken) {
            return $accessToken->tokenable;
        });

        return $next($request);
    }
}