<?php

namespace App\Services;

use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmService
{
    /**
     * Send an FCM push notification using HTTP v1 API.
     *
     * @param string $fcmToken The recipient's FCM device token
     * @param string $title The notification title
     * @param string $body The notification body
     * @param array $data Optional data payload
     * @return bool
     */
    public static function sendNotification(string $fcmToken, string $title, string $body, array $data = [])
    {
        try {
            $keyFilePath = storage_path('app/firebase-credentials.json');
            
            if (!file_exists($keyFilePath)) {
                Log::error('FCM credentials file not found at: ' . $keyFilePath);
                return false;
            }

            // Read the JSON file to extract the project_id
            $keyFileContents = json_decode(file_get_contents($keyFilePath), true);
            $projectId = $keyFileContents['project_id'] ?? null;

            if (!$projectId) {
                Log::error('FCM project_id not found in credentials file.');
                return false;
            }

            // Scopes required for FCM HTTP v1 API
            $scopes = ['https://www.googleapis.com/auth/firebase.messaging'];
            $credentials = new ServiceAccountCredentials($scopes, $keyFilePath);

            // Fetch the OAuth2 access token
            $authToken = $credentials->fetchAuthToken();
            
            if (!isset($authToken['access_token'])) {
                Log::error('FCM failed to fetch access token.');
                return false;
            }

            $accessToken = $authToken['access_token'];
            $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

            $payload = [
                'message' => [
                    'token' => $fcmToken,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'data' => $data,
                ]
            ];

            $response = Http::withToken($accessToken)
                            ->post($url, $payload);

            if ($response->successful()) {
                return true;
            } else {
                Log::error('FCM Notification Failed: ' . $response->body());
                return false;
            }

        } catch (\Exception $e) {
            Log::error('FCM Exception: ' . $e->getMessage());
            return false;
        }
    }
}
