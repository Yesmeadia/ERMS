<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Services\TOTPService;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectUser(Auth::user());
        }
        return view('auth.login');
    }

    /**
     * Handle authentication request.
     */
    public function login(Request $request)
    {
        $turnstileSiteKey = config('services.cloudflare.turnstile_site_key');
        $turnstileSecretKey = config('services.cloudflare.turnstile_secret_key');

        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $request->email)->first();
        if ($user && $user->lockout_until && (strtotime($user->lockout_until) > time())) {
            $minutes = ceil((strtotime($user->lockout_until) - time()) / 60);
            return back()->withErrors([
                'email' => "This account is temporarily locked due to multiple failed login attempts. Please try again in {$minutes} minutes.",
            ])->onlyInput('email');
        }

        if ($turnstileSiteKey && $turnstileSecretKey) {
            $request->validate([
                'cf-turnstile-response' => ['required'],
            ]);

            $turnstileResponse = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                'secret' => $turnstileSecretKey,
                'response' => $request->input('cf-turnstile-response'),
                'remoteip' => $request->ip(),
            ])->json();

            if (!($turnstileResponse['success'] ?? false)) {
                return back()->withErrors([
                    'email' => 'Turnstile verification failed. Please try again.',
                ])->onlyInput('email');
            }
        }

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();

            // Reset lockout status on success
            $user->failed_login_attempts = 0;
            $user->lockout_until = null;
            $user->save();

            // Store current password hash in session
            $request->session()->put('user_password_hash', $user->password);

            // Check if user belongs to an inactive school
            if ($user->hasRole('school-admin') && $user->school && !$user->school->status) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'Your school has been deactivated. Please contact the Examination Board.',
                ]);
            }

            $request->session()->regenerate();

            // Check if Super Admin requires MFA
            if ($user->hasRole('super-admin') && $user->two_factor_enabled) {
                $request->session()->put('auth.mfa_pending', true);
                $request->session()->put('auth.mfa_user_id', $user->id);

                activity()
                    ->causedBy($user)
                    ->log('Super Admin login credentials verified, MFA pending');

                return redirect()->route('login.mfa');
            }

            // Log activity
            activity()
                ->causedBy($user)
                ->log('User logged in');

            if ($user->hasRole('super-admin')) {
                try {
                    \Illuminate\Support\Facades\Mail::to($user->email)->send(
                        new \App\Mail\SuperAdminLoginAlertMail($user, $request->ip(), $request->userAgent(), now()->toDayDateTimeString())
                    );
                } catch (\Exception $e) {
                    report($e);
                }
            }

            return $this->redirectUser($user);
        }

        // Increment failed attempts on failure
        if ($user) {
            $user->increment('failed_login_attempts');
            if ($user->failed_login_attempts >= 5) {
                $user->lockout_until = now()->addMinutes(15);
                $user->save();
                
                activity()
                    ->performedOn($user)
                    ->log("Account locked due to 5 failed login attempts");
            }
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Handle logout.
     */
    public function logout(Request $request)
    {
        if (Auth::check()) {
            activity()
                ->causedBy(Auth::user())
                ->log('User logged out');
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Redirect user based on role.
     */
    protected function redirectUser(User $user)
    {
        if ($user->hasRole('super-admin')) {
            return redirect()->intended(route('admin.dashboard'));
        } elseif ($user->hasRole('school-admin')) {
            return redirect()->intended(route('school.dashboard'));
        } elseif ($user->hasRole('invigilator')) {
            return redirect()->intended(route('attendance.scanner'));
        }

        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('login')->withErrors(['email' => 'Unauthorized access.']);
    }



    /**
     * Show the forgot password form.
     */
    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    /**
     * Send password reset link to user.
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Send reset link through PasswordBroker
        $status = Password::broker()->sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', 'We have emailed your password reset link!')
            : back()->withErrors(['email' => __($status)]);
    }

    /**
     * Show the reset password form.
     */
    public function showResetPassword(Request $request, $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email
        ]);
    }

    /**
     * Handle the password reset.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $status = Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));
                $user->save();

                event(new PasswordReset($user));

                activity()
                    ->causedBy($user)
                    ->performedOn($user)
                    ->log('User reset their password via token recovery');
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('success', 'Your password has been reset successfully! You can now log in.')
            : back()->withErrors(['email' => __($status)]);
    }

    /**
     * Update password.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $user = Auth::user();
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        activity()
            ->causedBy($user)
            ->log('User updated their password');

        // Log out other sessions
        Auth::logoutOtherDevices($request->password);

        // Force logout of the current session to ensure full logout on password change
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Password updated successfully. Please log in with your new password.');
    }

    /**
     * Show MFA verification form during login.
     */
    public function showMfaVerification(Request $request)
    {
        if (!$request->session()->has('auth.mfa_pending') || !$request->session()->has('auth.mfa_user_id')) {
            return redirect()->route('login');
        }

        return view('auth.mfa');
    }

    /**
     * Verify MFA code and complete login.
     */
    public function verifyMfa(Request $request)
    {
        if (!$request->session()->has('auth.mfa_pending') || !$request->session()->has('auth.mfa_user_id')) {
            return redirect()->route('login');
        }

        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = User::findOrFail($request->session()->get('auth.mfa_user_id'));
        $totp = new TOTPService();

        if ($totp->verifyCode($user->two_factor_secret, $request->code)) {
            // Log user in
            Auth::login($user);
            
            // Store password hash in session
            $request->session()->put('user_password_hash', $user->password);
            
            // Clear pending session state
            $request->session()->forget('auth.mfa_pending');
            $request->session()->forget('auth.mfa_user_id');
            $request->session()->save();

            activity()
                ->causedBy($user)
                ->log('Super Admin logged in successfully with MFA');

            try {
                \Illuminate\Support\Facades\Mail::to($user->email)->send(
                    new \App\Mail\SuperAdminLoginAlertMail($user, $request->ip(), $request->userAgent(), now()->toDayDateTimeString())
                );
            } catch (\Exception $e) {
                report($e);
            }

            return $this->redirectUser($user);
        }

        return back()->withErrors([
            'code' => 'The provided MFA code is invalid. Please try again.',
        ]);
    }

    /**
     * Show MFA setup/management page.
     */
    public function showMfaSetup()
    {
        $user = Auth::user();
        $totp = new TOTPService();

        if (!$user->two_factor_secret) {
            $user->two_factor_secret = $totp->generateSecret();
            $user->save();
        }

        $qrCodeUrl = $totp->getQRCodeUrl($user->email, $user->two_factor_secret);

        return view('super-admin.mfa.setup', compact('user', 'qrCodeUrl'));
    }

    /**
     * Enable MFA for Super Admin.
     */
    public function enableMfa(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = Auth::user();
        $totp = new TOTPService();

        if ($totp->verifyCode($user->two_factor_secret, $request->code)) {
            $user->two_factor_enabled = true;
            $user->save();

            // Clear any pending MFA flags
            $request->session()->forget('auth.mfa_pending');
            $request->session()->forget('auth.mfa_user_id');

            activity()
                ->causedBy($user)
                ->log('Super Admin enabled MFA (2FA)');

            return back()->with('success', 'Multi-Factor Authentication (2FA) has been successfully enabled for your account.');
        }

        return back()->withErrors([
            'code' => 'The verification code was invalid. Please try scanning the QR code again.',
        ]);
    }

    /**
     * Disable MFA for Super Admin.
     */
    public function disableMfa(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = Auth::user();
        $user->two_factor_enabled = false;
        $user->two_factor_secret = null;
        $user->save();

        activity()
            ->causedBy($user)
            ->log('Super Admin disabled MFA (2FA)');

        return back()->with('success', 'Multi-Factor Authentication (2FA) has been disabled.');
    }
}
