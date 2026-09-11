<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FirebaseService
{
    private const FCM_V1_ENDPOINT = 'https://fcm.googleapis.com/v1/projects/{project_id}/messages:send';
    private const OAUTH_TOKEN_ENDPOINT = 'https://oauth2.googleapis.com/token';
    private const FCM_SCOPE = 'https://www.googleapis.com/auth/firebase.messaging';

    /**
     * Send push notification to a user (both mobile FCM token and web push token if available).
     */
    public static function sendToUser(User $user, string $title, string $body, array $data = [], ?string $link = null, ?string $image = null): void
    {
        if (!self::isConfigured()) {
            return; // Firebase push is OFF or not configured yet — exit silently without error
        }

        if ($user->fcm_token) {
            self::sendToToken($user->fcm_token, $title, $body, $data, $link, $image);
        }

        if ($user->web_push_token) {
            self::sendToToken($user->web_push_token, $title, $body, $data, $link, $image);
        }
    }

    /**
     * Send to a single FCM token through FCM HTTP v1, with legacy fallback during migration.
     */
    public static function sendToToken(string $token, string $title, string $body, array $data = [], ?string $link = null, ?string $image = null): bool
    {
        if (!$token) {
            return false;
        }

        try {
            if (self::hasHttpV1Credentials()) {
                return self::sendViaHttpV1($token, $title, $body, $data, $link, $image);
            }

            return self::sendViaLegacy($token, $title, $body, $data, $link, $image);

        } catch (\Throwable $e) {
            Log::error('FirebaseService::sendToToken exception: ' . $e->getMessage());
            return false;
        }
    }

    private static function sendViaHttpV1(string $token, string $title, string $body, array $data, ?string $link, ?string $image): bool
    {
        $credentials = self::serviceAccount();
        $accessToken = self::accessToken($credentials);
        $projectId = Setting::get('firebase_project_id') ?: ($credentials['project_id'] ?? null);

        if (!$accessToken || !$projectId) {
            return false;
        }

        $notification = ['title' => $title, 'body' => $body];
        if ($image) {
            $notification['image'] = $image;
        }

        $payload = [
            'message' => [
                'token' => $token,
                'notification' => $notification,
                'data' => self::stringifyData(array_merge($data, [
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    'link' => $link ?? '',
                    'image' => $image ?? '',
                ])),
                'android' => ['priority' => 'HIGH', 'notification' => ['sound' => 'default']],
                'webpush' => ['notification' => ['icon' => '/firebase-logo.png'], 'fcm_options' => ['link' => $link ?? '/']],
            ],
        ];

        $response = Http::withToken($accessToken)->timeout(10)
            ->post('https://fcm.googleapis.com/v1/projects/' . rawurlencode($projectId) . '/messages:send', $payload);

        if ($response->successful()) {
            return true;
        }

        if ($response->status() === 404 || str_contains($response->body(), 'UNREGISTERED')) {
            self::invalidateToken($token);
        }

        Log::warning('FCM HTTP v1 send failed', ['token_prefix' => substr($token, 0, 10), 'status' => $response->status()]);
        return false;
    }

    private static function sendViaLegacy(string $token, string $title, string $body, array $data, ?string $link, ?string $image): bool
    {
        $serverKey = Setting::get('firebase_server_key');
        if (!$serverKey) {
            return false;
        }

        $notification = ['title' => $title, 'body' => $body, 'sound' => 'default', 'click_action' => 'FLUTTER_NOTIFICATION_CLICK'];
        if ($image) {
            $notification['image'] = $image;
        }

        $response = Http::withHeaders(['Authorization' => 'key=' . $serverKey, 'Content-Type' => 'application/json'])
            ->timeout(10)->post('https://fcm.googleapis.com/fcm/send', [
                'to' => $token,
                'notification' => $notification,
                'data' => self::stringifyData(array_merge($data, ['click_action' => 'FLUTTER_NOTIFICATION_CLICK', 'link' => $link ?? '', 'image' => $image ?? ''])),
                'priority' => 'high',
            ]);

        if ($response->successful() && (($response->json('success') ?? 0) === 1)) {
            return true;
        }

        if (str_contains($response->body(), 'NotRegistered') || str_contains($response->body(), 'InvalidRegistration')) {
            self::invalidateToken($token);
        }

        Log::warning('Legacy FCM send failed', ['token_prefix' => substr($token, 0, 10), 'status' => $response->status()]);
        return false;
    }

    private static function hasHttpV1Credentials(): bool
    {
        $credentials = self::serviceAccount();
        return !empty($credentials['client_email']) && !empty($credentials['private_key']);
    }

    private static function serviceAccount(): array
    {
        $raw = Setting::get('firebase_service_account_json');
        if (!$raw) {
            return [];
        }

        $credentials = json_decode((string) $raw, true);
        return is_array($credentials) ? $credentials : [];
    }

    private static function accessToken(array $credentials): ?string
    {
        $cacheKey = 'firebase.fcm.access_token.' . sha1((string) ($credentials['client_email'] ?? ''));
        return Cache::remember($cacheKey, 3500, function () use ($credentials) {
            $now = time();
            $encode = static fn (array $value): string => rtrim(strtr(base64_encode(json_encode($value)), '+/', '-_'), '=');
            $header = $encode(['alg' => 'RS256', 'typ' => 'JWT']);
            $claims = $encode(['iss' => $credentials['client_email'], 'scope' => self::FCM_SCOPE, 'aud' => self::OAUTH_TOKEN_ENDPOINT, 'iat' => $now, 'exp' => $now + 3600]);
            $unsigned = $header . '.' . $claims;

            if (!openssl_sign($unsigned, $signature, $credentials['private_key'], OPENSSL_ALGO_SHA256)) {
                return null;
            }

            $response = Http::asForm()->timeout(10)->post(self::OAUTH_TOKEN_ENDPOINT, [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $unsigned . '.' . rtrim(strtr(base64_encode($signature), '+/', '-_'), '='),
            ]);

            return $response->successful() ? $response->json('access_token') : null;
        });
    }

    private static function stringifyData(array $data): array
    {
        return array_map(static fn ($value): string => is_scalar($value) ? (string) $value : json_encode($value), $data);
    }

    /**
     * Remove invalid/expired token from users table.
     */
    private static function invalidateToken(string $token): void
    {
        try {
            \App\Models\User::where('fcm_token', $token)->update(['fcm_token' => null]);
            \App\Models\User::where('web_push_token', $token)->update(['web_push_token' => null]);
        } catch (\Exception $e) {
            Log::warning('FirebaseService: Could not invalidate token: ' . $e->getMessage());
        }
    }

    /**
     * Send push notification to ALL admin users (role = admin).
     * Useful for: new user registration, new property, new complaint, payment, etc.
     */
    public static function sendToAdmins(string $title, string $body, array $data = [], ?string $link = null): void
    {
        if (!self::isConfigured()) {
            return;
        }

        try {
            $admins = \App\Models\User::where('role', 'admin')
                ->where(function ($q) {
                    $q->whereNotNull('fcm_token')
                      ->orWhereNotNull('web_push_token');
                })
                ->get(['id', 'fcm_token', 'web_push_token']);

            foreach ($admins as $admin) {
                if ($admin->fcm_token) {
                    self::sendToToken($admin->fcm_token, $title, $body, $data, $link);
                }
                if ($admin->web_push_token) {
                    self::sendToToken($admin->web_push_token, $title, $body, $data, $link);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('FirebaseService::sendToAdmins exception: ' . $e->getMessage());
        }
    }

    /**
     * Check if Firebase is enabled in settings and configured (server key exists).
     */
    public static function isConfigured(): bool
    {
        $enabled   = Setting::get('firebase_push_enabled', '1') === '1';
        $hasV1Credentials = self::hasHttpV1Credentials();
        $hasLegacyKey = !empty(Setting::get('firebase_server_key'));
        return $enabled && ($hasV1Credentials || $hasLegacyKey);
    }
}
