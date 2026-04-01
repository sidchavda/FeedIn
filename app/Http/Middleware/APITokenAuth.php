<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

class APITokenAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Try to get token from Authorization header first
        $token = $request->header('Authorization');
        
        // If not found, try other common header names
        if (!$token) {
            $token = $request->header('X-API-Token') ?? $request->header('token');
        }
        
        // Check if token exists
        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Authorization token is required',
                'error' => 'MISSING_TOKEN'
            ], 401);
        }

        // Extract token from Bearer format if present
        if (str_starts_with($token, 'Bearer ')) {
            $token = substr($token, 7); // Remove 'Bearer ' prefix
        }
        
        // Find user by hashed token
        $user = User::where('api_token', hash('sha256', $token))->first();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token',
                'error' => 'INVALID_TOKEN'
            ], 401);
        }

        // Add user to request for use in controllers
        $request->merge(['created_by' => $user->id]);
        
        return $next($request);
    }
} 