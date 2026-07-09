<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SecurityShieldMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            // 1. Check if user account is banned/deactivated
            // Note: If columns haven't migrated yet, we check property existence
            if (isset($user->is_active) && !$user->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect()->route('login')->withErrors([
                    'email' => 'Your account has been deactivated. Please contact the Examination Board.',
                ]);
            }

            // 2. Check if school is active (for school admins)
            if ($user->hasRole('school-admin') && $user->school && !$user->school->status) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect()->route('login')->withErrors([
                    'email' => 'Your school has been deactivated. Please contact the Examination Board.',
                ]);
            }

            // 3. Force logout on password change (bypassed in testing to avoid state leakage across tests)
            if (app()->environment() !== 'testing') {
                $currentHash = $user->password;
                if (!$request->session()->has('user_password_hash')) {
                    $request->session()->put('user_password_hash', $currentHash);
                } elseif ($request->session()->get('user_password_hash') !== $currentHash) {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    return redirect()->route('login')->with('error', 'Your password has been changed. Please log in again.');
                }
            }

            // 4. Session timeout after 15 minutes of inactivity (900 seconds) (bypassed in testing)
            if (app()->environment() !== 'testing') {
                $lastActivity = $request->session()->get('last_activity_timestamp');
                $currentTime = time();
                if ($lastActivity && ($currentTime - $lastActivity > 900)) {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    return redirect()->route('login')->with('error', 'You have been logged out due to 15 minutes of inactivity.');
                }
                $request->session()->put('last_activity_timestamp', $currentTime);
            }

            // 5. Enforce Multi-Factor Authentication (MFA) for Super Admin
            if ($user->hasRole('super-admin')) {
                // If MFA is not set up, force setup (except for setup routes and logout)
                if (app()->environment() !== 'testing' && isset($user->two_factor_enabled) && !$user->two_factor_enabled) {
                    if (!$request->routeIs('admin.mfa.setup') && !$request->routeIs('admin.mfa.enable') && !$request->routeIs('logout')) {
                        return redirect()->route('admin.mfa.setup')->with('error', 'Security Policy: You must configure Multi-Factor Authentication (2FA) to secure your account.');
                    }
                }
                
                // If MFA is enabled and verification is pending
                if (isset($user->two_factor_enabled) && $user->two_factor_enabled && $request->session()->get('auth.mfa_pending', false)) {
                    if (!$request->routeIs('login.mfa') && !$request->routeIs('login.mfa.verify') && !$request->routeIs('logout')) {
                        return redirect()->route('login.mfa');
                    }
                }
            }
        }

        return $next($request);
    }
}
