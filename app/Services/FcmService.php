<?php

namespace App\Services;

use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmService
{
    /**
     * Retrieve cached OAuth2 access token and project ID for Firebase HTTP v1 API.
     *
     * @return array{project_id: string, access_token: string}|null
     */
    protected static function getAuthConfig(): ?array
    {
        return Cache::remember('fcm_oauth_auth_config', 3000, function () {
            $keyFilePath = storage_path('app/firebase-credentials.json');

            if (!file_exists($keyFilePath)) {
                Log::error('FCM credentials file not found at: ' . $keyFilePath);
                return null;
            }

            $keyFileContents = json_decode(file_get_contents($keyFilePath), true);
            $projectId = $keyFileContents['project_id'] ?? null;

            if (!$projectId) {
                Log::error('FCM project_id not found in credentials file.');
                return null;
            }

            $scopes = ['https://www.googleapis.com/auth/firebase.messaging'];
            $credentials = new ServiceAccountCredentials($scopes, $keyFilePath);
            $authToken = $credentials->fetchAuthToken();

            if (!isset($authToken['access_token'])) {
                Log::error('FCM failed to fetch access token.');
                return null;
            }

            return [
                'project_id' => $projectId,
                'access_token' => $authToken['access_token'],
            ];
        });
    }

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
            $auth = self::getAuthConfig();
            if (!$auth) {
                return false;
            }

            $projectId = $auth['project_id'];
            $accessToken = $auth['access_token'];
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
                ->timeout(5)
                ->post($url, $payload);

            if ($response->successful()) {
                return true;
            }

            if ($response->status() === 401) {
                Cache::forget('fcm_oauth_auth_config');
            }

            $errorBody = $response->json();
            $errorCode = $errorBody['error']['details'][0]['errorCode'] ?? null;
            if ($errorCode === 'UNREGISTERED' || $response->status() === 404) {
                \App\Models\FcmToken::where('token', $fcmToken)->delete();
                \App\Models\User::where('fcm_token', $fcmToken)->update(['fcm_token' => null]);
                Log::info('Pruned unregistered FCM token: ' . substr($fcmToken, 0, 15) . '...');
            } else {
                Log::error('FCM Notification Failed: ' . $response->body());
            }
            return false;

        } catch (\Exception $e) {
            Log::error('FCM Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send FCM notifications to multiple tokens concurrently using Http::pool.
     *
     * @param array $fcmTokens
     * @param string $title
     * @param string $body
     * @param array $data
     * @return void
     */
    public static function sendMultipleNotifications(array $fcmTokens, string $title, string $body, array $data = []): void
    {
        $fcmTokens = array_values(array_unique(array_filter($fcmTokens)));
        if (empty($fcmTokens)) {
            return;
        }

        try {
            $auth = self::getAuthConfig();
            if (!$auth) {
                return;
            }

            $projectId = $auth['project_id'];
            $accessToken = $auth['access_token'];
            $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

            $responses = Http::pool(function (Pool $pool) use ($fcmTokens, $accessToken, $url, $title, $body, $data) {
                foreach ($fcmTokens as $token) {
                    $pool->as($token)
                        ->withToken($accessToken)
                        ->timeout(5)
                        ->post($url, [
                            'message' => [
                                'token' => $token,
                                'notification' => [
                                    'title' => $title,
                                    'body' => $body,
                                ],
                                'data' => $data,
                            ],
                        ]);
                }
            });

            foreach ($responses as $token => $response) {
                if ($response instanceof \Illuminate\Http\Client\Response) {
                    if ($response->status() === 401) {
                        Cache::forget('fcm_oauth_auth_config');
                    } elseif (!$response->successful()) {
                        $errorBody = $response->json();
                        $errorCode = $errorBody['error']['details'][0]['errorCode'] ?? null;
                        if ($errorCode === 'UNREGISTERED' || $response->status() === 404) {
                            \App\Models\FcmToken::where('token', $token)->delete();
                            \App\Models\User::where('fcm_token', $token)->update(['fcm_token' => null]);
                            Log::info('Pruned unregistered FCM token: ' . substr($token, 0, 15) . '...');
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('FCM Pool Exception: ' . $e->getMessage());
        }
    }
}
