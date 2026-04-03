<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiToken;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApiTokenController extends Controller
{
    public function generate(Request $request)
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'expires_in_hours' => 'nullable|integer|min:1|max:8760',
        ]);

        $token = Str::random(64);
        $expiresInHours = $request->input('expires_in_hours', 24);

        $apiToken = ApiToken::create([
            'token' => $token,
            'name' => $request->input('name'),
            'abilities' => json_encode(['*']),
            'expires_at' => now()->addHours($expiresInHours),
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'token' => $token,
                'name' => $apiToken->name,
                'expires_at' => $apiToken->expires_at->toDateTimeString(),
                'expires_in_seconds' => now()->diffInSeconds($apiToken->expires_at),
            ],
            'message' => 'Token generated successfully'
        ]);
    }

    public function revoke(Request $request)
    {
        $request->validate([
            'token' => 'required|string|size:64',
        ]);

        $apiToken = ApiToken::where('token', $request->token)->first();

        if (!$apiToken) {
            return response()->json([
                'success' => false,
                'message' => 'Token not found'
            ], 404);
        }

        $apiToken->delete();

        return response()->json([
            'success' => true,
            'message' => 'Token revoked successfully'
        ]);
    }

    public function verify(Request $request)
    {
        $token = $request->bearerToken() ?? $request->header('X-API-Token');

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'API token required'
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

        return response()->json([
            'success' => true,
            'data' => [
                'name' => $apiToken->name,
                'expires_at' => $apiToken->expires_at->toDateTimeString(),
                'expires_in_seconds' => now()->diffInSeconds($apiToken->expires_at),
                'last_used_at' => $apiToken->last_used_at?->toDateTimeString(),
            ],
            'message' => 'Token is valid'
        ]);
    }

    public function list()
    {
        $tokens = ApiToken::where('expires_at', '>', now())
            ->orderBy('created_at', 'desc')
            ->get(['id', 'name', 'expires_at', 'last_used_at', 'created_at']);

        return response()->json([
            'success' => true,
            'data' => $tokens
        ]);
    }
}
