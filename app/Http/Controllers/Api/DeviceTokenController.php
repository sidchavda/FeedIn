<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DeviceTokenController extends Controller
{
    /**
     * Register device token
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_token' => 'required|string|unique:device_tokens',
            'device_type' => 'nullable|string|in:ios,android,web',
            'device_name' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        $deviceToken = DeviceToken::create([
            'user_id' => $user->id,
            'device_token' => $request->device_token,
            'device_type' => $request->device_type,
            'device_name' => $request->device_name,
            'last_used_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Device token registered successfully',
            'data' => $deviceToken
        ]);
    }

    /**
     * Update device token last used
     */
    public function update(Request $request, $id)
    {
        $deviceToken = DeviceToken::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->first();

        if (!$deviceToken) {
            return response()->json([
                'success' => false,
                'message' => 'Device token not found'
            ], 404);
        }

        $deviceToken->last_used_at = now();
        $deviceToken->save();

        return response()->json([
            'success' => true,
            'message' => 'Device token updated successfully'
        ]);
    }

    /**
     * Delete device token
     */
    public function delete(Request $request, $id)
    {
        $deviceToken = DeviceToken::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->first();

        if (!$deviceToken) {
            return response()->json([
                'success' => false,
                'message' => 'Device token not found'
            ], 404);
        }

        $deviceToken->delete();

        return response()->json([
            'success' => true,
            'message' => 'Device token deleted successfully'
        ]);
    }

    /**
     * Get user's device tokens
     */
    public function list(Request $request)
    {
        $deviceTokens = DeviceToken::where('user_id', $request->user()->id)
            ->orderBy('last_used_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $deviceTokens
        ]);
    }
}
