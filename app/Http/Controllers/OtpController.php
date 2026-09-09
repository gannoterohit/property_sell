<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Otp;
use App\Models\User;
use App\Models\Setting;
use App\Mail\OtpMail;
use App\Services\SmsService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class OtpController extends Controller
{
    /**
     * Resolve OTP delivery mode from admin settings.
     */
    private function otpMode(): string
    {
        return Setting::get('otp_delivery', 'email');
    }

    /**
     * Send OTP to the provided email (and/or phone based on admin setting)
     */
    public function sendOtp(Request $request)
    {
        $mode = $this->otpMode();

        // Normalise: web sends 'identifier' (email or phone) OR 'email'
        $rawIdentifier = trim($request->input('identifier') ?? $request->input('email') ?? '');

        if (empty($rawIdentifier)) {
            return response()->json(['success' => false, 'message' => 'Please enter your email or mobile number.'], 422);
        }

        $isEmail = filter_var($rawIdentifier, FILTER_VALIDATE_EMAIL) !== false;
        $isPhone = !$isEmail && preg_match('/^[0-9+\-\s]{10,15}$/', preg_replace('/\s/', '', $rawIdentifier));

        if (!$isEmail && !$isPhone) {
            return response()->json(['success' => false, 'message' => 'Enter a valid email address or 10-digit mobile number.'], 422);
        }

        $email = $isEmail ? strtolower(trim($rawIdentifier)) : null;
        $phone = $isPhone ? preg_replace('/[^0-9+]/', '', $rawIdentifier) : null;
        $context = $request->input('context') ?? $request->input('mode') ?? 'login';

        // ── Lookup user ────────────────────────────────────────────────
        $existingUser = null;
        if ($email) {
            $existingUser = User::where('email', $email)->first();
        } elseif ($phone) {
            $existingUser = User::where('phone', $phone)
                ->orWhere('phone', '91' . $phone)
                ->orWhere('phone', '+91' . $phone)
                ->first();
        }

        // If user is trying to register but account already exists
        if ($context === 'register' && $existingUser) {
            return response()->json([
                'success' => false,
                'already_registered' => true,
                'message' => 'This account is already registered. Please login instead.'
            ], 422);
        }

        if ($existingUser && $existingUser->is_blocked) {
            return response()->json(['success' => false, 'message' => 'Your account has been blocked.'], 403);
        }
        if ($existingUser && $existingUser->role === 'admin') {
            return response()->json(['success' => false, 'message' => 'Admin accounts must use the admin login page.'], 403);
        }

        $emailSent = false;
        $smsSent   = false;

        // ── EMAIL OTP ──────────────────────────────────────────────────
        $sendEmail = $email && ($mode === 'email' || $mode === 'both');
        // If phone entered but mode is both → also send to user's email
        $sendEmailViaPhone = $phone && $mode === 'both' && $existingUser && $existingUser->email;

        if ($sendEmail || $sendEmailViaPhone) {
            $targetEmail = $email ?? ($existingUser->email ?? null);
            if ($targetEmail) {
                $code = Otp::generate($targetEmail);
                Setting::setMailConfig();
                \Illuminate\Support\Facades\Mail::purge();
                try {
                    Mail::to($targetEmail)->send(new OtpMail($code));
                    $emailSent = true;
                } catch (\Exception $e) {
                    Log::error("OTP email failed for {$targetEmail}: " . $e->getMessage());
                    if ($mode === 'email') {
                        return response()->json(['success' => false, 'message' => 'Unable to send OTP email. Please try again.'], 500);
                    }
                }
            }
        }

        // ── SMS OTP ────────────────────────────────────────────────────
        $sendSms = ($mode === 'phone' || $mode === 'both');
        $smsPhone = $phone;
        if (!$smsPhone && $existingUser && $existingUser->phone) {
            $smsPhone = $existingUser->phone;
        }

        if ($sendSms && $smsPhone) {
            // Use same OTP code that was generated for email (if email sent), otherwise generate new one keyed by phone
            if (!$emailSent) {
                $code = Otp::generateForPhone($smsPhone);
            }
            try {
                SmsService::sendOtp($smsPhone, $code ?? Otp::generateForPhone($smsPhone));
                $smsSent = true;
            } catch (\Exception $e) {
                Log::error("OTP SMS failed for {$smsPhone}: " . $e->getMessage());
            }
        }

        if (!$emailSent && !$smsSent) {
            return response()->json(['success' => false, 'message' => 'Unable to send OTP. Please check your details and try again.'], 500);
        }

        $sentVia = array_filter(['email' => $emailSent, 'SMS' => $smsSent]);
        $message = 'OTP sent via ' . implode(' and ', array_keys($sentVia));

        return response()->json(['success' => true, 'message' => $message, 'mode' => $mode]);
    }

    /**
     * Verify OTP for login — accepts email OR phone identifier
     */
    public function verifyLoginOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'      => 'nullable|email',
            'phone'      => 'nullable|string',
            'identifier' => 'nullable|string',
            'otp'        => 'required|string|min:6|max:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Invalid input', 'errors' => $validator->errors()], 422);
        }

        // Resolve identifier
        $rawId   = $request->input('identifier') ?? $request->input('email') ?? $request->input('phone');
        $isEmail = filter_var($rawId, FILTER_VALIDATE_EMAIL) !== false;
        $email   = $isEmail ? $rawId : null;
        $phone   = !$isEmail ? preg_replace('/[^0-9+]/', '', (string) $rawId) : null;

        // Verify OTP
        if ($email) {
            $verified = Otp::verify($email, $request->otp);
        } else {
            $verified = Otp::verifyByIdentifier($phone, $request->otp);
        }

        if (!$verified) {
            return response()->json(['success' => false, 'message' => 'Invalid or expired OTP'], 401);
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

        if ($user && $user->role === 'admin') {
            return response()->json(['success' => false, 'message' => 'Admin and staff accounts must use the admin login page.'], 403);
        }

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'No account found. Please register first.'], 404);
        }

        if ($user->is_blocked) {
            return response()->json(['success' => false, 'message' => 'Your account has been blocked. Please contact support.'], 403);
        }

        auth()->login($user);

        $redirect = match ($user->role) {
            'admin' => route('admin.dashboard'),
            'broker' => $user->is_broker_active ? route('agent.dashboard') : route('agent.pending'),
            'owner' => route('owner.dashboard'),
            default => route('home'),
        };

        return response()->json(['success' => true, 'message' => 'Login successful', 'redirect' => $redirect]);
    }

    /**
     * Verify OTP for registration
     */
    public function verifyRegistrationOtp(Request $request)
    {
        if ($request->has('email')) {
            $request->merge(['email' => strtolower(trim((string)$request->email))]);
        }
        if ($request->has('phone')) {
            $request->merge(['phone' => trim((string)$request->phone)]);
        }

        $messages = [
            'email.unique'   => 'This email address is already registered. Please login instead.',
            'email.required' => 'Please enter your email address.',
            'email.email'    => 'Please enter a valid email address.',
            'name.required'  => 'Please enter your full name.',
            'otp.required'   => 'Please enter the 6-digit verification code.',
            'otp.min'        => 'Verification code must be 6 digits.',
            'otp.max'        => 'Verification code must be 6 digits.',
        ];

        $validator = Validator::make($request->all(), [
            'name'            => 'required|string|max:255',
            'email'           => 'required|email|unique:users,email',
            'phone'           => 'nullable|string',
            'role'            => 'nullable|in:user,owner,broker',
            'otp'             => 'required|string|min:6|max:6',
            'agency_name'     => 'nullable|string|max:255',
            'agency_address'  => 'nullable|string|max:500',
            'broker_license'  => 'nullable|string|max:100',
            'agency_gst'      => 'nullable|string|max:50',
        ], $messages);

        if ($validator->fails()) {
            $firstError = $validator->errors()->first();
            $alreadyRegistered = $validator->errors()->has('email') && str_contains(strtolower($firstError), 'already registered');
            return response()->json([
                'success' => false,
                'already_registered' => $alreadyRegistered,
                'message' => $firstError ?: 'Please check your information.',
                'errors'  => $validator->errors()
            ], 422);
        }

        $email = strtolower(trim((string)$request->email));
        $otp   = preg_replace('/\D/', '', (string) $request->otp);

        if (!Otp::verify($email, $otp)) {
            return response()->json([
                'success' => false,
                'message' => 'The verification code you entered is invalid or has expired. Please check or click Resend code.'
            ], 401);
        }

        // Handle Referral
        $referredBy         = null;
        $initialFreeUnlocks = 0;

        $referralCode = $request->referral_code ?? session('referral_code');

        if ($referralCode && Setting::isEnabled('referral_enabled', true)) {
            $referrer = User::where('referral_code', $referralCode)->first();
            if ($referrer) {
                $referredBy = $referrer->id;
                $referrer->increment('free_unlocks', 1);
                $initialFreeUnlocks = 1;
                \App\Services\NotificationService::notifyReferralBonusReceived($referrer, 1, 'A new user registered using your referral code');
            }
        }

        $userData = [
            'name'              => $request->name,
            'email'             => $request->email,
            'phone'             => $request->phone,
            'role'              => $request->role ?? 'user',
            'password'          => Hash::make(Str::random(64)),
            'email_verified_at' => now(),
            'referred_by_id'    => $referredBy,
            'wallet'            => 0,
            'free_unlocks'      => $initialFreeUnlocks,
        ];

        if ($request->role === 'broker') {
            $userData['broker_verification_status'] = \App\Models\BrokerSetting::isEnabled('broker_verification_enabled', true) ? 'pending' : 'approved';
            $userData['agency_name']    = $request->agency_name ?? null;
            $userData['agency_address'] = $request->agency_address ?? null;
            $userData['broker_license'] = $request->broker_license ?? null;
            $userData['agency_gst']     = $request->agency_gst ?? null;
            if ($userData['broker_verification_status'] === 'approved') {
                $userData['is_broker_active'] = true;
                $userData['broker_verified_at'] = now();
                $userData['broker_approved_at'] = now();
            }
        }

        $user = User::create($userData);

        // Send Welcome email and notification
        \App\Services\NotificationService::notifyWelcome($user);

        // Clear referral session
        session()->forget('referral_code');

        // Log in the user
        auth()->login($user);

        $msg = 'Registration successful!';
        if ($initialFreeUnlocks > 0) {
            $msg .= " You have received {$initialFreeUnlocks} Free Contact Unlock as a joining bonus!";
        }

        // Flash signup success for Google Ads tracking
        session(['signup_success' => true]);

        // Role-based redirect
        $redirect = route('home');
        if ($user->role === 'broker') {
            if ($user->is_broker_active) {
                $redirect = route('agent.dashboard');
            } else {
                $redirect = route('agent.pending');
            }
        } elseif ($user->role === 'owner') {
            $redirect = route('owner.dashboard');
        }

        return response()->json([
            'success'  => true,
            'message'  => $msg,
            'redirect' => $redirect,
        ]);
    }
}
