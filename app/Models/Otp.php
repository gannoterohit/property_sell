<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class Otp extends Model
{
    use HasFactory;

    protected $fillable = ['email', 'phone', 'identifier', 'code', 'expires_at', 'used'];

    /**
     * Generate OTP by email (legacy + still used for email-mode)
     */
    public static function generate(string $email): string
    {
        $cleanEmail = strtolower(trim($email));
        self::where('email', $cleanEmail)->orWhere('identifier', $cleanEmail)->delete();

        $code = random_int(100000, 999999);

        self::create([
            'email'      => $cleanEmail,
            'identifier' => $cleanEmail,
            'code'       => Hash::make((string) $code),
            'expires_at' => now()->addMinutes(10),
        ]);

        return (string) $code;
    }

    /**
     * Generate OTP by phone number
     */
    public static function generateForPhone(string $phone): string
    {
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);

        self::where('phone', $cleanPhone)
            ->orWhere('identifier', $cleanPhone)
            ->delete();

        $code = random_int(100000, 999999);

        self::create([
            'phone'      => $cleanPhone,
            'identifier' => $cleanPhone,
            'code'       => Hash::make((string) $code),
            'expires_at' => now()->addMinutes(10),
        ]);

        return (string) $code;
    }

    /**
     * Verify OTP by email (legacy)
     */
    public static function verify(string $email, string $code): bool
    {
        $cleanEmail = strtolower(trim($email));
        $cleanCode  = trim($code);

        return DB::transaction(function () use ($cleanEmail, $cleanCode): bool {
            $otp = self::where(function ($q) use ($cleanEmail) {
                    $q->where('email', $cleanEmail)->orWhere('identifier', $cleanEmail);
                })
                ->where('expires_at', '>', now())
                ->where('used', false)
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if (!$otp) {
                return false;
            }

            $valid = str_starts_with($otp->code, '$')
                ? Hash::check($cleanCode, $otp->code)
                : hash_equals((string) $otp->code, $cleanCode);

            if (!$valid) {
                return false;
            }

            $otp->update(['used' => true]);
            return true;
        });
    }

    /**
     * Verify OTP by identifier (email or phone)
     */
    public static function verifyByIdentifier(string $identifier, string $code): bool
    {
        $cleanIdentifier = filter_var($identifier, FILTER_VALIDATE_EMAIL)
            ? $identifier
            : preg_replace('/[^0-9]/', '', $identifier);

        return DB::transaction(function () use ($cleanIdentifier, $code): bool {
            $otp = self::where('identifier', $cleanIdentifier)
                ->where('expires_at', '>', now())
                ->where('used', false)
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if (!$otp) {
                return false;
            }

            $valid = str_starts_with($otp->code, '$')
                ? Hash::check($code, $otp->code)
                : hash_equals((string) $otp->code, $code);

            if (!$valid) {
                return false;
            }

            $otp->update(['used' => true]);
            return true;
        });
    }
}
