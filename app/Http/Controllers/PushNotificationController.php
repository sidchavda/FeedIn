<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PushNotificationController extends Controller
{
    /**
     * Send push notification for a news item
     */
    public function sendNotification($newsId)
    {
        try {
            $news = News::findOrFail($newsId);
            
            // Check if push notification is enabled for this news
            if (!$news->push_notification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Push notification not enabled for this news item'
                ], 400);
            }
            
            // Send push notification logic here
            // This is a placeholder - you can integrate with Firebase, OneSignal, etc.
            $result = $this->sendPushNotification($news);
            
            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Push notification sent successfully',
                    'news_id' => $newsId,
                    'title' => $news->title
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send push notification'
                ], 500);
            }
            
        } catch (\Exception $e) {
            Log::error('Push notification error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error sending push notification: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Toggle push notification status for a news item
     */
    public function toggleNotification($newsId)
    {
        try {
            $news = News::findOrFail($newsId);
            
            $news->push_notification = !$news->push_notification;
            $news->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Push notification status updated',
                'news_id' => $newsId,
                'push_notification' => $news->push_notification
            ]);
            
        } catch (\Exception $e) {
            Log::error('Toggle notification error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating notification status: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get news items with push notification enabled
     */
    public function getPendingNotifications()
    {
        $newsItems = News::where('push_notification', true)
            ->where('status', 1)
            ->orderBy('created_at', 'desc')
            ->get(['id', 'title', 'push_notification', 'created_at']);
            
        return response()->json([
            'success' => true,
            'count' => $newsItems->count(),
            'data' => $newsItems
        ]);
    }
    
    /**
     * Internal method to send actual push notification
     * Integrate with your preferred service (Firebase, OneSignal, etc.)
     */
    private function sendPushNotification(News $news)
    {
        // TODO: Implement actual push notification logic
        // Example: Firebase Cloud Messaging, OneSignal, Pusher Beams, etc.
        
        // Placeholder implementation
        Log::info('Push notification sent for news: ' . $news->title);
        
        // Return true to simulate success
        // Replace with actual notification service integration
        return true;
    }
}
