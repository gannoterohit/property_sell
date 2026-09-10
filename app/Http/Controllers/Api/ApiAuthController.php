<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Otp;
use App\Models\SocialAccount;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use App\Models\Setting;
use App\Mail\OtpMail;
use App\Services\SmsService;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Auth\Events\PasswordReset;
use Laravel\Socialite\Facades\Socialite;

class ApiAuthController extends BaseApiController
{
    public function sendPasswordResetLink(Request $request)
    {
        $data = $request->validate(['email' => ['required', 'email']]);
        $status = Password::sendResetLink(['email' => $data['email']]);

        if ($status !== Password::RESET_LINK_SENT) {
            return $this->sendError(__($status), ['email' => [__($status)]], 422);
        }

        return $this->sendSuccess([], __($status));
    }

    public function resetPassword(Request $request)
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::reset(
            $data,
            function (User $user) use ($data) {
                $user->forceFill([
                    'password' => Hash::make($data['password']),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return $this->sendError(__($status), ['email' => [__($status)]], 422);
        }

        return $this->sendSuccess([], __($status));
    }

    private function otpMode(): string
    {
        return Setting::get('otp_delivery', 'email');
    }

    /**
     * Send OTP — works for all 3 modes (email / phone / both)
     * Flutter sends: email OR phone — system detects and sends OTP accordingly
     * "both" mode: if email given → email OTP + SMS to registered phone
     *              if phone given → SMS OTP + email to registered email
     */
    public function sendOtp(Request $request)
    {
        $mode = $this->otpMode();

        // Accept either email or phone (or both) — flexible for all modes
        $validator = Validator::make($request->all(), [
            'email' => 'nullable|email',
            'phone' => 'nullable|string|min:10|max:15',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Please check your input and try again.', $validator->errors(), 422);
        }

        // Resolve identifier from input
        $email = $request->filled('email') ? $request->email : null;
        $phone = $request->filled('phone') ? preg_replace('/[^0-9+]/', '', $request->phone) : null;

        if (!$email && !$phone) {
            return $this->sendError('Please provide your email or mobile number.', [], 422);
        }

        // Lookup user
        $existingUser = null;
        if ($email) {
            $existingUser = User::where('email', $email)->first();
        } elseif ($phone) {
            $existingUser = User::where('phone', $phone)
                ->orWhere('phone', '91' . $phone)
                ->orWhere('phone', '+91' . $phone)
                ->first();
        }

        if ($existingUser && $existingUser->is_blocked) {
            return $this->sendError('Your account has been blocked.', [], 403);
        }

        $emailSent = false;
        $smsSent   = false;
        $code      = null;

        // ── EMAIL OTP ──────────────────────────────────────────────
        $sendEmail = $email && ($mode === 'email' || $mode === 'both');
        // "both" mode + phone given → also send to user's registered email
        $sendEmailViaPhone = $phone && $mode === 'both' && $existingUser && $existingUser->email;

        if ($sendEmail || $sendEmailViaPhone) {
            $targetEmail = $email ?? $existingUser->email;
            $code = Otp::generate($targetEmail);
            Setting::setMailConfig();
            try {
                Mail::to($targetEmail)->send(new OtpMail($code));
                $emailSent = true;
            } catch (\Exception $e) {
                Log::error("API OTP email failed for {$targetEmail}: " . $e->getMessage());
                if ($mode === 'email') {
                    return $this->sendError('Failed to send OTP email. Please try again.', [], 500);
                }
            }
        }

        // ── SMS OTP ────────────────────────────────────────────────
        $sendSms = $mode === 'phone' || $mode === 'both';
        // Resolve SMS target phone
        $smsPhone = $phone;
        if (!$smsPhone && $existingUser && $existingUser->phone) {
            $smsPhone = $existingUser->phone; // email given but user has phone
        }

        if ($sendSms && $smsPhone) {
            // Reuse same $code if email OTP already generated, else generate for phone
            if (!$emailSent || !$code) {
                $code = Otp::generateForPhone($smsPhone);
            }
            $smsSent = SmsService::sendOtp($smsPhone, $code);
            if (!$smsSent && $mode === 'phone') {
                return $this->sendError('Failed to send OTP SMS. Please try again.', [], 500);
            }
        }

        if (!$emailSent && !$smsSent) {
            return $this->sendError('Unable to send OTP. Please check your details and try again.', [], 500);
        }

        return $this->sendSuccess([
            'mode'       => $mode,
            'email_sent' => $emailSent,
            'sms_sent'   => $smsSent,
        ], 'OTP sent successfully.');
    }

    /**
     * Login using OTP — all 3 modes: email, phone, or both
     * Flutter sends: { email: "...", otp: "..." } OR { phone: "...", otp: "..." }
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'     => 'nullable|email',
            'phone'     => 'nullable|string|min:10',
            'otp'       => 'required|string|min:6|max:6',
            'fcm_token' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Please check your input and try again.', $validator->errors(), 422);
        }

        if (!$request->filled('email') && !$request->filled('phone')) {
            return $this->sendError('Please provide email or phone number.', [], 422);
        }

        $email = $request->filled('email') ? $request->email : null;
        $phone = $request->filled('phone') ? preg_replace('/[^0-9+]/', '', $request->phone) : null;

        // Verify OTP using the right identifier
        if ($email) {
            $verified = Otp::verify($email, $request->otp);
        } else {
            $verified = Otp::verifyByIdentifier($phone, $request->otp);
        }

        if (!$verified) {
            return $this->sendError('Invalid or expired OTP.', [], 401);
        }

        // Find user
        $user = null;
        if ($email) {
            $user = User::where('email', $email)->first();
        } elseif ($phone) {
            $user = User::where('phone', $phone)
                ->orWhere('phone', '91' . $phone)
                ->orWhere('phone', '+91' . $phone)
                ->first();
        }

        // Auto-register if user not found
        if (!$user) {
            $user = User::create([
                'name'              => 'User',
                'email'             => $email ?? null,
                'phone'             => $phone ?? null,
                'role'              => 'user',
                'password'          => Hash::make(Str::random(64)),
                'email_verified_at' => now(),
            ]);
            \App\Services\NotificationService::notifyWelcome($user);
        }

        if ($user->is_blocked) {
            return $this->sendError('Your account is blocked.', [], 403);
        }
        if ($user->role === 'admin') {
            return $this->sendError('Administrators must use the admin email and password login.', [], 403);
        }

        if ($request->filled('fcm_token')) {
            $user->update(['fcm_token' => $request->fcm_token]);
        }

        $token = $user->createToken('flutter_app')->plainTextToken;

        return $this->sendSuccess([
            'token' => $token,
            'user'  => new UserResource($user),
        ], 'Login successful.');
    }

    /**
     * Register a new user with OTP
     * Flutter sends: { name, email OR phone, otp, role?, referral_code?, fcm_token? }
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'          => 'required|string|max:255',
            'email'         => 'nullable|email|unique:users,email',
            'phone'         => 'nullable|string',
            'role'          => 'nullable|in:user,owner,broker',
            'otp'           => 'required|string|min:6|max:6',
            'referral_code' => 'nullable|string',
            'fcm_token'     => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Please check your input and try again.', $validator->errors(), 422);
        }

        $email = $request->filled('email') ? $request->email : null;
        $phone = $request->filled('phone') ? preg_replace('/[^0-9+]/', '', $request->phone) : null;

        // Verify OTP
        if ($email) {
            $verified = Otp::verify($email, $request->otp);
        } elseif ($phone) {
            $verified = Otp::verifyByIdentifier($phone, $request->otp);
        } else {
            return $this->sendError('Please provide email or phone number.', [], 422);
        }

        if (!$verified) {
            return $this->sendError('Invalid or expired OTP.', [], 401);
        }

        // Handle Referral
        $referredBy         = null;
        $initialFreeUnlocks = 0;

        if ($request->referral_code && Setting::isEnabled('referral_enabled', true)) {
            $referrer = User::where('referral_code', $request->referral_code)->first();
            if ($referrer) {
                $referredBy = $referrer->id;
                $referrer->increment('free_unlocks', 1);
                $initialFreeUnlocks = 1;
                \App\Services\NotificationService::notifyReferralBonusReceived($referrer, 1, 'A new user registered using your referral code');
            }
        }

        $user = User::create([
            'name'              => $request->name,
            'email'             => $email,
            'phone'             => $phone,
            'role'              => $request->input('role', 'user'),
            'password'          => Hash::make(Str::random(64)),
            'email_verified_at' => now(),
            'referred_by_id'    => $referredBy,
            'wallet'            => 0,
            'free_unlocks'      => $initialFreeUnlocks,
            'fcm_token'         => $request->fcm_token ?? null,
        ]);

        \App\Services\NotificationService::notifyWelcome($user);

        if ($user->role === 'broker') {
            \App\Services\NotificationService::notifyAdminNewBrokerRegistered($user);
        }

        $token = $user->createToken('flutter_app')->plainTextToken;

        return $this->sendSuccess([
            'token' => $token,
            'user'  => new UserResource($user),
        ], 'Registration successful.');
    }

    /**
     * Get authenticated user profile
     */
    public function user(Request $request)
    {
        return $this->sendSuccess(new UserResource($request->user()));
    }

    /**
     * Social login — accepts provider + access_token/id_token
     * and returns a Sanctum API token for the mobile app.
     */
    public function socialLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'provider' => 'required|in:google,facebook',
            'token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Invalid input.', $validator->errors(), 422);
        }

        $provider = $request->input('provider');
        $token = $request->input('token');

        $enabledKey = $provider . '_login_enabled';
        if (!filter_var(Setting::get($enabledKey, '0'), FILTER_VALIDATE_BOOLEAN)) {
            return $this->sendError(ucfirst($provider) . ' login is currently disabled.', [], 403);
        }

        $clientId = Setting::get($provider . '_client_id');
        $clientSecret = Setting::get($provider . '_client_secret');
        $redirectUrl = Setting::get($provider . '_redirect_url');

        if (!$clientId || !$clientSecret || !$redirectUrl) {
            return $this->sendError(ucfirst($provider) . ' login is not configured properly.', [], 500);
        }

        config(['services.' . $provider => [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'redirect' => $redirectUrl,
        ]]);

        try {
            $socialUser = Socialite::driver($provider)->userFromToken($token);
        } catch (\Exception $e) {
            return $this->sendError('Invalid social token. Please try again.', [], 401);
        }

        if (empty($socialUser->email)) {
            return $this->sendError('Email is required for social login. Please enable email access from your account.', [], 422);
        }

        $socialAccount = SocialAccount::where('provider', $provider)
            ->where('provider_id', $socialUser->getId())
            ->first();

        if ($socialAccount) {
            $user = $socialAccount->user;
        } else {
            $user = User::where('email', $socialUser->getEmail())->first();

            if (!$user) {
                $user = User::create([
                    'name' => $socialUser->getName() ?: $socialUser->getNickname() ?: 'User',
                    'email' => $socialUser->getEmail(),
                    'password' => Hash::make(Str::random(32)),
                    'role' => 'user',
                    'avatar' => $socialUser->getAvatar() ?: null,
                    'provider' => $provider,
                    'provider_id' => $socialUser->getId(),
                ]);
                \App\Services\NotificationService::notifyWelcome($user);
            } else {
                $user->update([
                    'provider' => $provider,
                    'provider_id' => $socialUser->getId(),
                ]);
            }

            SocialAccount::create([
                'user_id' => $user->id,
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
                'name' => $socialUser->getName(),
                'email' => $socialUser->getEmail(),
                'avatar' => $socialUser->getAvatar(),
            ]);
        }

        $tokenResult = $user->createToken('flutter_app')->plainTextToken;

        return $this->sendSuccess([
            'token' => $tokenResult,
            'user' => new UserResource($user),
        ], 'Social login successful.');
    }

    /**
     * Logout and revoke tokens — clears FCM token
     */
    public function logout(Request $request)
    {
        $user = $request->user();
        if ($user) {
            $user->update(['fcm_token' => null]);
            $user->tokens()->delete();
        }

        return $this->sendSuccess([], 'Logged out successfully.');
    }
}
