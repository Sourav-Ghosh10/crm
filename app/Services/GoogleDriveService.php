<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GoogleDriveService
{
    private const DRIVE_SCOPE = 'https://www.googleapis.com/auth/drive';
    private const API_BASE = 'https://www.googleapis.com/drive/v3';
    private const UPLOAD_BASE = 'https://www.googleapis.com/upload/drive/v3';

    /**
     * Get the root folder ID configured in .env
     */
    public static function getRootFolderId(): ?string
    {
        return config('services.google_drive.folder_id', env('GOOGLE_DRIVE_FOLDER_ID'));
    }

    /**
     * Fetch or retrieve cached OAuth2 access token using OAuth2 Refresh Token
     */
    public static function getAccessToken(): ?string
    {
        return Cache::remember('google_drive_oauth_access_token', now()->addMinutes(50), function () {
            $clientId = config('services.google_drive.client_id', env('GOOGLE_DRIVE_CLIENT_ID'));
            $clientSecret = config('services.google_drive.client_secret', env('GOOGLE_DRIVE_CLIENT_SECRET'));
            $refreshToken = config('services.google_drive.refresh_token', env('GOOGLE_DRIVE_REFRESH_TOKEN'));

            if (!$clientId || !$clientSecret || !$refreshToken) {
                Log::error('Google Drive OAuth credentials missing (client_id, client_secret, or refresh_token).');
                return null;
            }

            try {
                $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                    'refresh_token' => $refreshToken,
                    'grant_type' => 'refresh_token',
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    return $data['access_token'] ?? null;
                }

                Log::error('Google Drive token refresh failed: ' . $response->body());
                return null;
            } catch (\Throwable $e) {
                Log::error('Exception refreshing Google Drive token: ' . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * Check if Google Drive integration is configured
     */
    public static function isConfigured(): bool
    {
        return !empty(config('services.google_drive.client_id', env('GOOGLE_DRIVE_CLIENT_ID')))
            && !empty(config('services.google_drive.client_secret', env('GOOGLE_DRIVE_CLIENT_SECRET')))
            && !empty(config('services.google_drive.refresh_token', env('GOOGLE_DRIVE_REFRESH_TOKEN')));
    }

    /**
     * Find or create a subfolder (supports single or slash-separated names like 'Projects' or 'Projects/Activities')
     */
    public static function getOrCreateFolder(string $folderPath, ?string $parentId = null): ?string
    {
        $currentParent = $parentId ?: self::getRootFolderId();
        if (!$currentParent) {
            Log::error('Google Drive root folder ID not configured.');
            return null;
        }

        $segments = array_filter(explode('/', trim($folderPath, '/')));
        if (empty($segments)) {
            return $currentParent;
        }

        foreach ($segments as $segment) {
            $segment = trim($segment);
            if ($segment === '') continue;

            $cacheKey = "gdrive_folder_{$currentParent}_" . md5($segment);
            $nextParent = Cache::remember($cacheKey, now()->addHours(6), function () use ($segment, $currentParent) {
                $token = self::getAccessToken();
                if (!$token) {
                    return null;
                }

                // 1. Search for existing folder
                $query = "mimeType = 'application/vnd.google-apps.folder' and name = '{$segment}' and '{$currentParent}' in parents and trashed = false";
                $searchRes = Http::withToken($token)
                    ->get(self::API_BASE . '/files', [
                        'q' => $query,
                        'fields' => 'files(id, name)',
                        'pageSize' => 1,
                    ]);

                if ($searchRes->successful()) {
                    $files = $searchRes->json('files', []);
                    if (!empty($files[0]['id'])) {
                        return $files[0]['id'];
                    }
                }

                // 2. Create the folder if not exists
                $createRes = Http::withToken($token)
                    ->post(self::API_BASE . '/files', [
                        'name' => $segment,
                        'mimeType' => 'application/vnd.google-apps.folder',
                        'parents' => [$currentParent],
                    ]);

                if ($createRes->successful()) {
                    return $createRes->json('id');
                }

                Log::error('Failed to create Google Drive folder: ' . $createRes->body());
                return $currentParent;
            });

            if (!$nextParent) {
                return $currentParent;
            }
            $currentParent = $nextParent;
        }

        return $currentParent;
    }

    /**
     * Upload a file to Google Drive
     *
     * @param UploadedFile|string $file UploadedFile instance or raw content / file path
     * @param string $category Subfolder category (e.g., 'Projects', 'Chat')
     * @param string|null $customFilename
     * @return array|null [ 'file_id', 'name', 'mime_type', 'size', 'web_view_link' ]
     */
    public static function uploadFile($file, string $category = 'Projects', ?string $customFilename = null): ?array
    {
        $token = self::getAccessToken();
        if (!$token) {
            Log::error('Cannot upload to Google Drive: No access token available.');
            return null;
        }

        $targetFolderId = self::getOrCreateFolder($category, self::getRootFolderId());

        if ($file instanceof UploadedFile) {
            $filename = $customFilename ?: $file->getClientOriginalName();
            $mimeType = $file->getClientMimeType() ?: 'application/octet-stream';
            $content = file_get_contents($file->getRealPath());
        } elseif (is_string($file) && file_exists($file)) {
            $filename = $customFilename ?: basename($file);
            $mimeType = mime_content_type($file) ?: 'application/octet-stream';
            $content = file_get_contents($file);
        } else {
            $filename = $customFilename ?: 'file_' . time() . '.bin';
            $mimeType = 'application/octet-stream';
            $content = (string) $file;
        }

        // Multipart upload body
        $metadata = json_encode([
            'name' => $filename,
            'parents' => $targetFolderId ? [$targetFolderId] : [],
        ]);

        $boundary = '-------314159265358979323846';
        $delimiter = "\r\n--" . $boundary . "\r\n";
        $closeDelimiter = "\r\n--" . $boundary . "--";

        $multipartBody = $delimiter
            . "Content-Type: application/json; charset=UTF-8\r\n\r\n"
            . $metadata
            . $delimiter
            . "Content-Type: {$mimeType}\r\n"
            . "Content-Transfer-Encoding: base64\r\n\r\n"
            . base64_encode($content)
            . $closeDelimiter;

        try {
            $response = Http::withToken($token)
                ->withHeaders([
                    'Content-Type' => 'multipart/related; boundary=' . $boundary,
                ])
                ->send('POST', self::UPLOAD_BASE . '/files?uploadType=multipart&fields=id,name,mimeType,size,webViewLink,webContentLink', [
                    'body' => $multipartBody,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'file_id' => $data['id'],
                    'name' => $data['name'] ?? $filename,
                    'mime_type' => $data['mimeType'] ?? $mimeType,
                    'size' => $data['size'] ?? strlen($content),
                    'web_view_link' => $data['webViewLink'] ?? null,
                ];
            }

            Log::error('Google Drive upload error: ' . $response->body());
            return null;
        } catch (\Throwable $e) {
            Log::error('Google Drive upload exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Download or stream a file from Google Drive to browser
     *
     * @param string $fileId
     * @param string|null $filename
     * @param string|null $mimeType
     * @param string $disposition 'inline' (view in browser) or 'attachment' (force download)
     */
    public static function streamResponse(
        string $fileId,
        ?string $filename = null,
        ?string $mimeType = null,
        string $disposition = 'inline'
    ): StreamedResponse {
        $token = self::getAccessToken();

        // If mimeType or filename is missing, attempt to fetch metadata
        if (!$mimeType || !$filename) {
            $meta = self::getFileMetadata($fileId);
            if ($meta) {
                $filename = $filename ?: ($meta['name'] ?? 'download');
                $mimeType = $mimeType ?: ($meta['mimeType'] ?? 'application/octet-stream');
            }
        }

        $filename = $filename ?: 'download';
        $mimeType = $mimeType ?: 'application/octet-stream';

        $headers = [
            'Content-Type' => $mimeType,
            'Content-Disposition' => $disposition . '; filename="' . addslashes($filename) . '"',
            'Cache-Control' => 'private, max-age=3600',
        ];

        return response()->stream(function () use ($fileId, $token) {
            try {
                $response = Http::withToken($token)
                    ->withOptions([
                        'stream' => true,
                        'timeout' => 120,
                        'verify' => false,
                    ])
                    ->get(self::API_BASE . "/files/{$fileId}?alt=media");

                $stream = $response->toPsrResponse()->getBody();
                while (!$stream->eof()) {
                    echo $stream->read(1024 * 64);
                    if (ob_get_level() > 0) {
                        ob_flush();
                    }
                    flush();
                }
            } catch (\Throwable $e) {
                Log::error("Error streaming Google Drive file {$fileId}: " . $e->getMessage());
            }
        }, 200, $headers);
    }

    /**
     * Get file metadata
     */
    public static function getFileMetadata(string $fileId): ?array
    {
        $token = self::getAccessToken();
        if (!$token) {
            return null;
        }

        $res = Http::withToken($token)->get(self::API_BASE . "/files/{$fileId}?fields=id,name,mimeType,size,webViewLink");
        return $res->successful() ? $res->json() : null;
    }

    /**
     * Delete file from Google Drive
     */
    public static function deleteFile(string $fileId): bool
    {
        $token = self::getAccessToken();
        if (!$token) {
            return false;
        }

        $res = Http::withToken($token)->delete(self::API_BASE . "/files/{$fileId}");
        return $res->successful() || $res->status() === 404;
    }

    /**
     * Check if a path is stored on Google Drive
     */
    public static function isGoogleDrivePath(?string $path): bool
    {
        return !empty($path) && (str_starts_with($path, 'google:') || str_starts_with($path, 'gdrive:'));
    }

    /**
     * Extract File ID from google:FILE_ID format
     */
    public static function getFileIdFromPath(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }
        if (str_starts_with($path, 'google:')) {
            return substr($path, 7);
        }
        if (str_starts_with($path, 'gdrive:')) {
            return substr($path, 7);
        }
        return $path;
    }
}
