<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class UserAuthController extends Controller
{
    use ApiResponse;

    /**
     * User Login (Email & Password via Auth)
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->validationError(
                $validator->errors()->toArray(),
                'Validation failed',
                422
            );
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->unauthorized('Wrong email or password.', 401);
        }

        if (isset($user->is_active) && !$user->is_active) {
            return $this->unauthorized('Your account has been deactivated. Please contact support.', 403);
        }

        // Generate token and assign to user
        $token = Str::random(80);
        $user->api_token = $token;
        $user->save();

        // Check if email is verified
        if ($user->email_verified_at === null) {
            $this->sendOtp($user);

            return $this->badRequest('Email not verified. A new OTP has been sent to your email.', [
                'token' => $token,
                'user'  => $this->formatUser($user),
            ], 200);
        }

        return $this->success([
            'token' => $token,
            'user'  => $this->formatUser($user),
        ], 'Login Successful');
    }

    /**
     * User Registration
     */
    public function UserRegister(Request $request)
    {
        return $this->registerUser($request, 'user');
    }

    /**
     * Internal Registration Handler
     */
    protected function registerUser(Request $request, $role)
    {
        if (strtolower($role) === 'admin') {
            return $this->badRequest('Admin registration is not allowed.', [], 403);
        }

        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'phone'    => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray(), 'Validation failed', 422);
        }

        DB::beginTransaction();
        try {
            $token = Str::random(80);

            $user = User::create([
                'name'      => $request->name,
                'email'     => $request->email,
                'password'  => Hash::make($request->password),
                'api_token' => $token,
                'phone'     => $request->phone,
                'role_id'   => 5, // Customer / Normal User role
                'is_active' => 1,
                'is_guest'  => 0,
            ]);

            $this->sendOtp($user);

            DB::commit();

            return $this->success([
                'token' => $token,
                'user'  => $this->formatUser($user),
            ], 'Registration successful. Please verify your email with the OTP sent to your email.', 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->error('Registration failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Verify Email via OTP (Authenticated)
     */
    public function verifyEmail(Request $request)
    {
        $user = Auth::guard('api')->user() ?? $request->user();
        if (!$user) {
            return $this->unauthorized();
        }

        $validator = Validator::make($request->all(), [
            'otp' => 'required|numeric|digits:4',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray(), 'Validation failed', 422);
        }

        try {
            if ($user->otp != $request->otp) {
                return $this->badRequest('Invalid OTP.', [], 400);
            }

            if ($user->otp_created_at && now()->gt(Carbon::parse($user->otp_created_at)->addMinutes(15))) {
                return $this->badRequest('OTP has expired.', [], 400);
            }

            $user->email_verified_at = now();
            $user->otp = null;
            $user->otp_created_at = null;
            $user->save();

            return $this->success([
                'token' => $user->api_token,
                'user'  => $this->formatUser($user),
            ], 'Email Verified Successfully');
        } catch (\Throwable $e) {
            return $this->error('Email verification failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Guest Login (via device_id)
     */
    public function guestLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_id' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray(), 'Validation failed', 422);
        }

        try {
            $deviceId = trim((string) $request->device_id);

            $lower = strtolower($deviceId);
            $knownBadDeviceIds = [
                '', '0', '00000000-0000-0000-0000-000000000000',
                'null', 'undefined', 'unknown', 'default', 'none',
                '02:00:00:00:00:00', '9774d56d682e549c', 'ffffffffffffffff',
                '0000000000000000', 'android', 'ios', 'simulator',
                'emulator', 'generic', 'test', '1234567890123456',
            ];

            if (strlen($deviceId) < 8 || in_array($lower, $knownBadDeviceIds, true)) {
                Log::warning('Guest login rejected: suspicious device_id', [
                    'device_id' => $deviceId,
                    'ip'        => $request->ip(),
                ]);
                return $this->badRequest('Invalid device identification. Please try again.', [], 400);
            }

            $user = User::where('device_id', $deviceId)
                ->where('is_guest', true)
                ->first();

            $token = Str::random(80);

            if (!$user) {
                $registeredUser = User::where('device_id', $deviceId)
                    ->where('is_guest', false)
                    ->first();

                if ($registeredUser) {
                    $registeredUser->device_id = null;
                    $registeredUser->save();
                }

                $lastGuest = User::where('name', 'like', 'Guest_%')
                    ->orderBy('id', 'desc')
                    ->first();

                $guestNumber = $lastGuest ? ((int) str_replace('Guest_', '', $lastGuest->name)) + 1 : 1;
                $guestName   = 'Guest_' . $guestNumber;

                $user = User::create([
                    'name'      => $guestName,
                    'email'     => 'guest_' . Str::random(8) . '@guest.local',
                    'password'  => Hash::make(Str::random(16)),
                    'device_id' => $deviceId,
                    'api_token' => $token,
                    'is_guest'  => true,
                    'role_id'   => 5,
                    'is_active' => 1,
                ]);
            } else {
                $user->api_token = $token;
                $user->save();
            }

            return $this->success([
                'user'  => $this->formatUser($user),
                'token' => $token,
            ], 'Guest login successful.', 200);
        } catch (\Throwable $e) {
            return $this->error('Something went wrong: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Request Password Reset OTP
     */
    public function forgetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray(), 'Validation failed', 422);
        }

        try {
            $user = User::where('email', $request->email)->first();
            $this->clearPasswordResetCache();
            $this->sendOtp($user);

            Cache::put('password_reset_user_id', $user->id, now()->addMinutes(15));
            Cache::put('password_reset_otp', (string) $user->otp, now()->addMinutes(15));
            Cache::put('password_reset_email', $user->email, now()->addMinutes(15));

            return $this->success([], 'OTP sent successfully. Please check your email.', 200);
        } catch (\Throwable $e) {
            return $this->error('Failed to send OTP: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Verify Password Reset OTP (No email required, only OTP)
     */
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'otp' => 'required|numeric|digits:4',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray(), 'Validation failed', 422);
        }

        $userId    = Cache::get('password_reset_user_id');
        $cachedOtp = Cache::get('password_reset_otp');

        if (!$userId || !$cachedOtp) {
            return $this->badRequest('Please request an OTP first or OTP has expired.', [], 400);
        }

        if ($request->otp != $cachedOtp) {
            return $this->badRequest('Invalid OTP.', [], 400);
        }

        $user = User::find($userId);
        if (!$user) {
            return $this->error('User not found.', 404);
        }

        if ($user->otp_created_at && now()->gt(Carbon::parse($user->otp_created_at)->addMinutes(15))) {
            return $this->badRequest('OTP has expired.', [], 400);
        }

        Cache::put('password_reset_verified', true, now()->addMinutes(15));
        Cache::put('verified_user_id', $userId, now()->addMinutes(15));

        return $this->success([], 'OTP verified successfully. You can now reset your password.', 200);
    }

    /**
     * Reset Password (after OTP verification - No email or old password required)
     * Accepts: password, password_confirmation (or confirm_password / new_password)
     */
    public function resetPassword(Request $request)
    {
        // Normalize confirmation field name aliases
        if ($request->has('confirm_password')) {
            $request->merge(['password_confirmation' => $request->confirm_password]);
        } elseif ($request->has('confirm_new_password')) {
            $request->merge(['password_confirmation' => $request->confirm_new_password]);
        } elseif ($request->has('new_password_confirmation')) {
            $request->merge(['password_confirmation' => $request->new_password_confirmation]);
        }

        if ($request->has('new_password') && !$request->has('password')) {
            $request->merge(['password' => $request->new_password]);
        }

        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:6|confirmed',
        ], [
            'password.confirmed' => 'The password confirmation does not match.',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray(), 'Validation failed', 422);
        }

        $userId     = Cache::get('verified_user_id');
        $isVerified = Cache::get('password_reset_verified');

        if (!$userId || !$isVerified) {
            return $this->badRequest('Please verify OTP first.', [], 400);
        }

        $user = User::find($userId);
        if (!$user) {
            return $this->error('User not found.', 404);
        }

        $user->password = Hash::make($request->password);
        $user->otp = null;
        $user->otp_created_at = null;
        $user->save();

        $this->clearPasswordResetCache();

        return $this->success([], 'Password reset successfully. Please login with your new password.', 200);
    }

    /**
     * Resend OTP
     */
    public function resendOtp(Request $request)
    {
        $email = $request->email ?? Cache::get('password_reset_email');

        if (!$email) {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email',
            ]);

            if ($validator->fails()) {
                return $this->validationError($validator->errors()->toArray(), 'Validation failed', 422);
            }
            $email = $request->email;
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            return $this->error('User not found.', 404);
        }

        try {
            $this->sendOtp($user);
            Cache::put('password_reset_otp', (string) $user->otp, now()->addMinutes(15));
            Cache::put('password_reset_user_id', $user->id, now()->addMinutes(15));
            Cache::put('password_reset_email', $user->email, now()->addMinutes(15));

            return $this->success([], 'OTP resent successfully. Please check your email.', 200);
        } catch (\Throwable $e) {
            return $this->error('Failed to resend OTP: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Logout User (Revoke API token)
     */
    public function logout(Request $request)
    {
        try {
            $user = Auth::guard('api')->user() ?? $request->user();
            if ($user) {
                $user->api_token = null;
                $user->save();
            }
            return $this->success([], 'Successfully logged out.', 200);
        } catch (\Exception $e) {
            return $this->error('Logout failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Current Authenticated User Profile
     */
    public function showUser(Request $request)
    {
        $user = Auth::guard('api')->user() ?? $request->user();
        if (!$user) {
            return $this->unauthorized();
        }

        return $this->success(['user' => $this->formatUser($user)], 'User found', 200);
    }

    /**
     * Update User Profile
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::guard('api')->user() ?? $request->user();
        if (!$user) {
            return $this->unauthorized();
        }

        $booleanFields = ['messages', 'notification'];
        foreach ($booleanFields as $field) {
            if ($request->has($field)) {
                $request->merge([
                    $field => filter_var($request->input($field), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
                ]);
            }
        }

        $validator = Validator::make($request->all(), [
            'name'   => 'nullable|string|max:255',
            'email'  => ['nullable', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'phone'  => ['nullable', 'string', 'max:20', Rule::unique('users', 'phone')->ignore($user->id)],
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray(), 'Validation failed', 422);
        }

        try {
            if ($request->hasFile('avatar')) {
                $file = $request->file('avatar');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = public_path('uploads/avatars');

                if (!file_exists($path)) {
                    mkdir($path, 0755, true);
                }
                $file->move($path, $filename);

                if ($user->avatar && file_exists($path . '/' . $user->avatar)) {
                    @unlink($path . '/' . $user->avatar);
                }

                $user->avatar = $filename;
            }

            if ($user->is_guest) {
                $randomPassword = Str::random(10);

                if ($request->filled('name'))  $user->name  = $request->name;
                if ($request->filled('email')) $user->email = $request->email;
                if ($request->filled('phone')) $user->phone = $request->phone;

                $user->is_guest = false;
                $user->password = Hash::make($randomPassword);
                $user->device_id = null;

                try {
                    Mail::raw(
                        "Hi {$user->name},\n\nYour guest account has been upgraded to a full account.\n\nLogin Email: {$user->email}\nPassword: {$randomPassword}\n\nPlease change your password after logging in.\n\nThank you!",
                        function ($message) use ($user) {
                            $message->to($user->email)
                                ->subject('Your Account Has Been Activated');
                        }
                    );
                } catch (\Throwable $mailEx) {
                    Log::warning('Guest upgrade mail failed: ' . $mailEx->getMessage());
                }

                $message = 'Guest account upgraded successfully. Login details sent to your email.';
            } else {
                foreach (['name', 'email', 'phone', 'messages', 'notification'] as $field) {
                    if ($request->has($field)) {
                        $user->$field = $request->input($field);
                    }
                }

                $message = 'Profile updated successfully.';
            }

            $user->save();

            return $this->success(
                ['user' => $this->formatUser($user)],
                $message,
                200
            );
        } catch (\Throwable $e) {
            return $this->error('Profile update failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Change Password (Authenticated)
     */
    public function changePassword(Request $request)
    {
        $user = Auth::guard('api')->user() ?? $request->user();
        if (!$user) {
            return $this->unauthorized();
        }

        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password'     => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray(), 'Validation failed', 422);
        }

        if (!Hash::check($request->current_password, $user->password)) {
            return $this->badRequest('Current password is incorrect.', [], 400);
        }

        try {
            $user->password = Hash::make($request->new_password);
            $user->save();

            return $this->success([], 'Password changed successfully.', 200);
        } catch (\Throwable $e) {
            return $this->error('Password change failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Store FCM Token for Push Notifications
     */
    public function fmstoreFcmToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray(), 'Validation failed', 422);
        }

        $user = Auth::guard('api')->user() ?? $request->user();
        if (!$user) {
            return $this->unauthorized('Authentication required to store FCM token.', 401);
        }

        User::where('fcm_token', $request->token)
            ->where('id', '!=', $user->id)
            ->update(['fcm_token' => null]);

        $user->update([
            'fcm_token' => $request->token,
        ]);

        return $this->success(['token' => $user->fcm_token], 'FCM token stored successfully', 200);
    }

    /**
     * Delete FCM Token
     */
    public function fmdeleteFcmToken(Request $request)
    {
        $user = Auth::guard('api')->user() ?? $request->user();
        if (!$user) {
            return $this->unauthorized('Authentication required to clear FCM token.', 401);
        }

        $user->update([
            'fcm_token' => null,
        ]);

        return $this->success([], 'Notification stopped successfully', 200);
    }

    /**
     * Google Login via ID Token or Access Token
     */
    public function googleLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray(), 'Validation failed', 422);
        }

        try {
            $token = $request->token;
            $email = null;
            $name  = null;

            if (str_starts_with($token, 'ya29.')) {
                // Access Token flow
                $response = Http::get('https://www.googleapis.com/oauth2/v3/userinfo', [
                    'access_token' => $token,
                ]);

                if ($response->failed()) {
                    return $this->error('Google Access Token verification failed', 401);
                }

                $userData = $response->json();
                $email = $userData['email'] ?? null;
                $name  = $userData['name'] ?? null;
            } else {
                // ID Token flow
                $response = Http::get('https://oauth2.googleapis.com/tokeninfo', [
                    'id_token' => $token,
                ]);

                if ($response->failed()) {
                    return $this->error('Google ID Token verification failed', 401);
                }

                $userData = $response->json();
                $email = $userData['email'] ?? null;
                $name  = $userData['name'] ?? null;
            }

            if (!$email) {
                return $this->error('Email not found in Google response', 400);
            }

            return $this->handleSocialLogin($email, $name, 'google');
        } catch (\Throwable $e) {
            return $this->error('Google login failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Apple Login
     */
    public function appleLogin(Request $request)
    {
        $rawToken = $request->input('token')
            ?? $request->input('id_token')
            ?? $request->input('identityToken')
            ?? $request->input('identity_token');

        if (!$rawToken) {
            return $this->validationError(
                ['token' => ['The token field is required. Accepted parameter names: token, id_token, identityToken, identity_token.']],
                'Validation failed',
                422
            );
        }

        $validator = Validator::make(
            ['name' => $request->input('name')],
            ['name' => 'nullable|string|max:255']
        );

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray(), 'Validation failed', 422);
        }

        try {
            $name = $request->input('name');
            $parts = explode('.', $rawToken);
            if (count($parts) !== 3) {
                return $this->unauthorized('Apple sign-in token is malformed.', 401);
            }

            $payloadJson = base64_decode(strtr($parts[1], '-_', '+/') . str_repeat('=', (4 - strlen($parts[1]) % 4) % 4));
            $payload     = json_decode($payloadJson, true);

            $email = $payload['email'] ?? null;
            $sub   = $payload['sub']   ?? null;

            if (!$email && $sub) {
                $email = 'apple_' . $sub . '@appleid.com';
            }

            if (!$email) {
                return $this->error('Email or Identifier not found in Apple token', 400);
            }

            return $this->handleSocialLogin($email, $name, 'apple', $sub);
        } catch (\Throwable $e) {
            return $this->error('Apple login failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Internal Social Login Handler
     */
    protected function handleSocialLogin($email, $name, $provider, $providerId = null)
    {
        $user = null;

        if ($provider === 'apple' && $providerId) {
            $user = User::where('apple_id', $providerId)->first();
        }

        if (!$user && $email) {
            $user = User::where('email', $email)->first();
        }

        $token = Str::random(80);

        if (!$user) {
            $user = User::create([
                'name'              => $name ?? 'User_' . Str::random(5),
                'email'             => $email,
                'password'          => Hash::make(Str::random(16)),
                'api_token'         => $token,
                'email_verified_at' => now(),
                'apple_id'          => ($provider === 'apple') ? $providerId : null,
                'role_id'           => 5,
                'is_active'         => 1,
            ]);
        } else {
            $user->api_token = $token;
            if ($provider === 'apple' && $providerId && !$user->apple_id) {
                $user->apple_id = $providerId;
            }
            if (!$user->email_verified_at) {
                $user->email_verified_at = now();
            }
            $user->save();
        }

        return $this->success([
            'token' => $token,
            'user'  => $this->formatUser($user),
        ], 'Login Successful');
    }

    /**
     * App Account Deletion via credentials
     */
    public function appAccountDelete(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email'    => 'required|email',
                'password' => 'required|string',
                'reason'   => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return $this->validationError($validator->errors()->toArray(), 'Validation failed', 422);
            }

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return $this->badRequest('Email or Password is incorrect', [], 400);
            }

            $user->delete();

            return $this->success([], 'Profile deleted successfully', 200);
        } catch (\Throwable $e) {
            return $this->error('Server Error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Account Deletion for Authenticated User
     */
    public function accountDelete(Request $request)
    {
        try {
            $user = Auth::guard('api')->user() ?? $request->user();

            if (!$user) {
                return $this->unauthorized();
            }

            $user->delete();

            return $this->success([], 'Profile deleted successfully', 200);
        } catch (\Throwable $e) {
            return $this->error('Server Error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Helper to send OTP
     */
    protected function sendOtp(User $user)
    {
        $otp = rand(1000, 9999);

        $user->update([
            'otp'            => $otp,
            'otp_created_at' => now(),
        ]);

        try {
            Mail::to($user->email)->send(new \App\Mail\OtpMail($otp, $user->name));
        } catch (\Throwable $e) {
            Log::warning('Failed to send OTP email: ' . $e->getMessage());
        }
    }

    /**
     * Clear Cache for Password Reset
     */
    protected function clearPasswordResetCache($email = null)
    {
        Cache::forget('password_reset_user_id');
        Cache::forget('password_reset_otp');
        Cache::forget('password_reset_email');
        Cache::forget('password_reset_verified');
        Cache::forget('verified_user_id');

        if ($email) {
            Cache::forget('password_reset_user_id_' . $email);
            Cache::forget('password_reset_otp_' . $email);
            Cache::forget('password_reset_verified_' . $email);
            Cache::forget('verified_user_id_' . $email);
        }
    }

    /**
     * Format User data for API response
     */
    protected function formatUser($user)
    {
        return [
            'id'                => $user->id,
            'name'              => $user->name,
            'email'             => $user->email,
            'phone'             => $user->phone,
            'role_id'           => $user->role_id,
            'is_guest'          => (bool) $user->is_guest,
            'device_id'         => $user->device_id,
            'avatar'            => $user->avatar,
            'avatar_url'        => $user->avatar_url,
            'notification'      => (bool) ($user->notification ?? true),
            'messages'          => (bool) ($user->messages ?? true),
            'email_verified_at' => $user->email_verified_at ? $user->email_verified_at->toDateTimeString() : null,
            'created_at'        => $user->created_at ? $user->created_at->toDateTimeString() : null,
            'updated_at'        => $user->updated_at ? $user->updated_at->toDateTimeString() : null,
        ];
    }
}
