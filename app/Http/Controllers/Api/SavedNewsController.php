<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SavedNewsController extends Controller
{
    /**
     * Toggle save/unsave news for authenticated user
     * If already saved -> remove it (unsave)
     * If not saved -> add it (save)
     */
    public function toggle(Request $request, $newsId)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Please login.'
            ], 401);
        }

        // Check if news exists
        $news = News::find($newsId);
        if (!$news) {
            return response()->json([
                'success' => false,
                'message' => 'News not found'
            ], 404);
        }

        // Check if already saved
        $existing = DB::table('saved_news')
            ->where('user_id', $user->id)
            ->where('news_id', $newsId)
            ->first();

        if ($existing) {
            // Remove from saved (unsave)
            DB::table('saved_news')
                ->where('user_id', $user->id)
                ->where('news_id', $newsId)
                ->delete();

            return response()->json([
                'success' => true,
                'message' => 'News removed from saved list',
                'data' => [
                    'news_id' => (int) $newsId,
                    'is_saved' => false,
                    'action' => 'removed'
                ]
            ]);
        } else {
            // Add to saved
            DB::table('saved_news')->insert([
                'user_id' => $user->id,
                'news_id' => $newsId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'News saved successfully',
                'data' => [
                    'news_id' => (int) $newsId,
                    'is_saved' => true,
                    'action' => 'saved'
                ]
            ], 201);
        }
    }

    /**
     * Get all saved news for authenticated user
     */
    public function list(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Please login.'
            ], 401);
        }

        $savedNews = DB::table('saved_news')
            ->join('news', 'saved_news.news_id', '=', 'news.id')
            ->leftJoin('categories', 'news.category_id', '=', 'categories.id')
            ->where('saved_news.user_id', $user->id)
            ->select(
                'news.id',
                'news.title',
                'news.slug',
                'news.image',
                'news.description',
                'news.status',
                'categories.name as category',
                'saved_news.created_at as saved_at'
            )
            ->orderBy('saved_news.created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'count' => $savedNews->count(),
            'data' => $savedNews
        ]);
    }

    /**
     * Check if specific news is saved by user
     */
    public function check(Request $request, $newsId)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Please login.'
            ], 401);
        }

        $isSaved = DB::table('saved_news')
            ->where('user_id', $user->id)
            ->where('news_id', $newsId)
            ->exists();

        return response()->json([
            'success' => true,
            'data' => [
                'news_id' => (int) $newsId,
                'is_saved' => $isSaved
            ]
        ]);
    }
}
