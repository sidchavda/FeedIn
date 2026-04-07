<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\DeviceToken;
use App\Services\FCMService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PushNotificationController extends Controller
{
    protected $fcmService;

    public function __construct(FCMService $fcmService)
    {
        $this->fcmService = $fcmService;
    }

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
            
            // Get all device tokens
            $deviceTokens = DeviceToken::pluck('device_token')->toArray();
            
            if (empty($deviceTokens)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No device tokens found'
                ], 400);
            }
            
            // Prepare notification data
            $title = 'New News: ' . $news->title;
            $body = substr(strip_tags($news->description), 0, 100) . '...';
            $data = [
                'news_id' => $news->id,
                'slug' => $news->slug,
                'type' => 'news',
            ];
            
            // Send to all devices
            if (count($deviceTokens) > 1) {
                $result = $this->fcmService->sendToMultipleDevices($deviceTokens, $title, $body, $data);
            } else {
                $result = $this->fcmService->sendToDevice($deviceTokens[0], $title, $body, $data);
            }
            
            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Push notification sent successfully',
                    'news_id' => $newsId,
                    'title' => $news->title,
                    'recipients_count' => count($deviceTokens)
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
     * Send test notification to a single device
     */
    public function sendTestNotification(Request $request)
    {
        $request->validate([
            'device_token' => 'required|string',
            'title' => 'required|string',
            'body' => 'required|string',
        ]);
        
        $result = $this->fcmService->sendToDevice(
            $request->device_token,
            $request->title,
            $request->body,
            ['type' => 'test']
        );
        
        if ($result) {
            return response()->json([
                'success' => true,
                'message' => 'Test notification sent successfully'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send test notification'
            ], 500);
        }
    }
}
