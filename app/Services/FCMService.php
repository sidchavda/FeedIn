<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Exception\MessagingException;
use Illuminate\Support\Facades\Log;

class FCMService
{
    protected $messaging;

    public function __construct()
    {
        $factory = (new Factory)->withServiceAccount(
            base_path(config('services.firebase.credentials'))
        );
        $this->messaging = $factory->createMessaging();
    }

    /**
     * Send push notification to a single device
     */
    public function sendToDevice($deviceToken, $title, $body, $data = [])
    {
        try {
            $notification = Notification::create($title, $body);

            $message = CloudMessage::withTarget('token', $deviceToken)
                ->withNotification($notification)
                ->withData($data);

            $this->messaging->send($message);
            return true;
        } catch (MessagingException $e) {
            Log::error('FCM Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send push notification to multiple devices
     */
    public function sendToMultipleDevices(array $deviceTokens, $title, $body, $data = [])
    {
        try {
            $notification = Notification::create($title, $body);

            $message = CloudMessage::new()
                ->withNotification($notification)
                ->withData($data);

            $response = $this->messaging->sendMulticast($message, $deviceTokens);
            
            return [
                'success' => true,
                'success_count' => $response->successes()->count(),
                'failure_count' => $response->failures()->count(),
                'responses' => $response->responses(),
            ];
        } catch (MessagingException $e) {
            Log::error('FCM Multicast Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send push notification to a topic
     */
    public function sendToTopic($topic, $title, $body, $data = [])
    {
        try {
            $notification = Notification::create($title, $body);

            $message = CloudMessage::withTarget('topic', $topic)
                ->withNotification($notification)
                ->withData($data);

            $this->messaging->send($message);
            return true;
        } catch (MessagingException $e) {
            Log::error('FCM Topic Error: ' . $e->getMessage());
            return false;
        }
    }
}
