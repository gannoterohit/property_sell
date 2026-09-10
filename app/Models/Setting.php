<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class Setting extends Model
{
    public const CACHE_KEY = 'settings.all';
    public const CACHE_TTL = 3600; // 1 hour

    public const SECRET_KEYS = [
        'mail_password',
        'razorpay_secret',
        'razorpay_webhook_secret',
        'google_maps_api_key',
        'firebase_server_key',
        'firebase_service_account_json',
        'sms_api_key',
        'google_client_secret',
        'facebook_client_secret',
        'admin_access_key',
    ];

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'description',
    ];

    public function getValueAttribute($value)
    {
        if (! in_array($this->key, self::SECRET_KEYS, true) || $value === null || $value === '') {
            return $value;
        }

        try {
            return Crypt::decryptString($value);
        } catch (DecryptException) {
            // Existing installations may still contain legacy plaintext. It is
            // encrypted automatically the next time the setting is saved.
            return $value;
        }
    }

    public function setValueAttribute($value): void
    {
        if (! in_array($this->key, self::SECRET_KEYS, true) || $value === null || $value === '') {
            $this->attributes['value'] = $value;
            return;
        }

        try {
            Crypt::decryptString((string) $value);
            $this->attributes['value'] = $value;
        } catch (DecryptException) {
            $this->attributes['value'] = Crypt::encryptString((string) $value);
        }
    }

    /**
     * Get setting value by key
     */
    public static function get($key, $default = null)
    {
        if (!Schema::hasTable('settings')) {
            return $default;
        }

        $settings = Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            try {
                return self::all(['key', 'value'])->mapWithKeys(fn ($setting) => [$setting->key => $setting->value])->toArray();
            } catch (\Throwable $e) {
                return [];
            }
        });

        return $settings[$key] ?? $default;
    }

    public static function mediaUrl(?string $path, string $default = ''): string
    {
        if (! $path) {
            return $default !== '' ? asset($default) : '';
        }

        if (preg_match('/^https?:\/\//', $path)) {
            return $path;
        }

        $path = ltrim(trim($path), '/');
        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }

        if (Storage::disk('public')->exists($path)) {
            return asset('storage/' . $path);
        }

        return $default !== '' ? asset($default) : '';
    }

    /**
     * Clear settings cache
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public static function isEnabled(string $key, bool $default = true): bool
    {
        $value = self::get($key, $default ? '1' : '0');

        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return $value === 1;
        }

        if (is_string($value)) {
            $normalized = strtolower(trim($value));

            if ($normalized === '') {
                return $default;
            }

            if (in_array($normalized, ['1', 'true', 'yes', 'on'], true)) {
                return true;
            }

            if (in_array($normalized, ['0', 'false', 'no', 'off'], true)) {
                return false;
            }
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? (bool) $value;
    }

    /**
     * Set setting value
     */
    public static function set($key, $value)
    {
        if (!Schema::hasTable('settings')) {
            return null;
        }

        $result = self::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        self::clearCache();

        return $result;
    }

    /**
     * Get all settings by group
     */
    public static function getByGroup($group)
    {
        return self::where('group', $group)->get();
    }

    /**
     * Dynamically apply mail settings from the database to Laravel's configuration
     */
    public static function setMailConfig()
    {
        $host = trim(self::get('mail_host', ''));
        $port = self::get('mail_port', 587);
        $username = trim(self::get('mail_username', ''));
        $password = trim(self::get('mail_password', ''));
        $contactEmail = trim((string) self::get('contact_email', ''));
        $from_address = (!empty($contactEmail) && $contactEmail !== 'hello@example.com') ? $contactEmail : ($username ?: 'hello@example.com');
        $from_name = self::get('website_name', config('app.name', 'ApnaNest'));

        if ($host && $username && $password) {
            config([
                'mail.mailers.smtp.host' => $host,
                'mail.mailers.smtp.port' => $port,
                'mail.mailers.smtp.username' => $username,
                'mail.mailers.smtp.password' => $password,
                'mail.mailers.smtp.encryption' => ($port == 465) ? 'ssl' : 'tls',
                'mail.from.address' => $from_address,
                'mail.from.name' => $from_name,
                'mail.default' => 'smtp',
            ]);
        }
    }
}

