<?php

namespace App\Broadcasting;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Auth\Middleware\AuthTokenMiddleware;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

class FcmChannel
{
    /**
     * Send the given notification.
     */
    public function send($notifiable, Notification $notification)
    {
        if (!method_exists($notification, 'toFcm')) {
            return;
        }

        $tokens = $notifiable->fcmTokens()->pluck('token')->toArray();

        if (empty($tokens)) {
            return;
        }

        $message = $notification->toFcm($notifiable);
        $projectId = config('services.firebase.project_id');
        $credentialsPath = base_path(config('services.firebase.credentials', 'storage/app/firebase-credentials.json'));

        if (!$projectId || !file_exists($credentialsPath)) {
            Log::error('Firebase missing project ID or credentials file.');
            return;
        }

        try {
            $scopes = ['https://www.googleapis.com/auth/firebase.messaging'];
            $credentials = new ServiceAccountCredentials($scopes, $credentialsPath);
            $token = $credentials->fetchAuthToken();

            if (!isset($token['access_token'])) {
                Log::error('Firebase Failed to fetch access token.');
                return;
            }

            $accessToken = $token['access_token'];

            $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

            foreach ($tokens as $fcmToken) {
                $payload = [
                    'message' => [
                        'token' => $fcmToken,
                        'data' => [
                            'title' => $message['title'] ?? 'New Notification',
                            'body' => $message['body'] ?? '',
                            'url' => $message['url'] ?? '/',
                        ],
                    ]
                ];

                $response = Http::withToken($accessToken)
                    ->post($url, $payload);

                if (!$response->successful()) {
                    Log::error('FCM Send Error: ' . $response->body());
                }
            }
        } catch (\Exception $e) {
            Log::error('FCM Exception: ' . $e->getMessage());
        }
    }
}
