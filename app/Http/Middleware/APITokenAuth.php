<?php

namespace App\Http\Middleware;

use App\Models\ApiToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

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

        $apiToken = ApiToken::where('token', $token)->first();
        
        if (!$apiToken) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid API token'
            ], 401);
        }

        if ($apiToken->isExpired()) {
            $apiToken->delete();
            return response()->json([
                'success' => false,
                'message' => 'API token has expired'
            ], 401);
        }

        $apiToken->update(['last_used_at' => now()]);
        $request->attributes->add(['api_token' => $apiToken]);

        return $next($request);
    }
} 