<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use App\Models\User;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(Request $request): View
    {
        $email = trim((string) old('email', session('login_attempted_email', '')));
        $throttleKey = \Illuminate\Support\Str::transliterate(\Illuminate\Support\Str::lower($email).'|'.$request->ip());

        $lockoutSeconds = session('lockout_seconds');
        $lockedUntil = session('locked_until');

        if (! $lockoutSeconds && $email && RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $lockoutSeconds = RateLimiter::availableIn($throttleKey);
            $lockedUntil = now()->addSeconds($lockoutSeconds)->timestamp * 1000;
        }

        return view('auth.login', compact('lockedUntil', 'lockoutSeconds'));
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $email = trim($request->string('email')->toString());
        $throttleKey = $request->throttleKey();
        $user = User::where('email', $email)->first();

        // 1. Check if currently rate-limited (active 60s cooldown)
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $seconds = $seconds > 0 ? $seconds : 60;
            $lockedUntil = now()->addSeconds($seconds)->timestamp * 1000;
            session(['login_attempted_email' => $email]);

            if ($user && $user->failed_login_lockouts >= 2) {
                if ($user->hasRole('Admin')) {
                    return back()->withInput($request->only('email', 'remember'))
                        ->with('admin_lockout', true)
                        ->with('admin_email', $user->email)
                        ->with('lockout_seconds', $seconds)
                        ->with('locked_until', $lockedUntil)
                        ->with('attempts_left', 0)
                        ->withErrors(['email' => trans('auth.admin_lockout')]);
                }

                return back()->withInput($request->only('email', 'remember'))
                    ->with('auto_suspended', true)
                    ->with('lockout_seconds', $seconds)
                    ->with('locked_until', $lockedUntil)
                    ->with('attempts_left', 0)
                    ->withErrors(['email' => trans('auth.auto_suspended')]);
            }

            return back()->withInput($request->only('email', 'remember'))
                ->with('lockout_seconds', $seconds)
                ->with('locked_until', $lockedUntil)
                ->with('attempts_left', 0)
                ->withErrors(['email' => trans('auth.throttle', ['seconds' => $seconds])]);
        }

        // 2. Check credentials
        $password = $request->string('password')->toString();
        $isPasswordCorrect = $user && Hash::check($password, $user->password);

        if (! $user || ! $isPasswordCorrect) {
            RateLimiter::hit($throttleKey, 300);
            $attempts = RateLimiter::attempts($throttleKey);

            // Check if 5th failed attempt triggers cooldown
            if ($attempts >= 5) {
                // Ensure a full, fresh 60 seconds lockout window
                RateLimiter::clear($throttleKey);
                for ($i = 0; $i < 5; $i++) {
                    RateLimiter::hit($throttleKey, 60);
                }
                $seconds = 60;
                $lockedUntil = now()->addSeconds(60)->timestamp * 1000;
                session(['login_attempted_email' => $email]);

                if ($user) {
                    $user->increment('failed_login_lockouts');
                    $lockouts = $user->failed_login_lockouts;

                    // After 2 lockouts
                    if ($lockouts >= 2) {
                        if ($user->hasRole('Admin')) {
                            return back()->withInput($request->only('email', 'remember'))
                                ->with('admin_lockout', true)
                                ->with('admin_email', $user->email)
                                ->with('lockout_seconds', $seconds)
                                ->with('locked_until', $lockedUntil)
                                ->with('attempts_left', 0)
                                ->withErrors(['email' => trans('auth.admin_lockout')]);
                        }

                        // Suspend non-admin account automatically
                        $user->update(['is_active' => false]);
                        DB::table('active_sessions')->where('user_id', $user->id)->delete();

                        return back()->withInput($request->only('email', 'remember'))
                            ->with('auto_suspended', true)
                            ->with('lockout_seconds', $seconds)
                            ->with('locked_until', $lockedUntil)
                            ->with('attempts_left', 0)
                            ->withErrors(['email' => trans('auth.auto_suspended')]);
                    }
                }

                // First lockout (60s countdown)
                return back()->withInput($request->only('email', 'remember'))
                    ->with('lockout_seconds', $seconds)
                    ->with('locked_until', $lockedUntil)
                    ->with('attempts_left', 0)
                    ->withErrors(['email' => trans('auth.throttle', ['seconds' => $seconds])]);
            }

            // Attempts 1 to 4 failed
            $attemptsLeft = max(0, 5 - $attempts);

            return back()->withInput($request->only('email', 'remember'))
                ->with('attempts_left', $attemptsLeft)
                ->withErrors(['email' => trans('auth.failed')]);
        }

        // 3. User is valid, but account is deactivated
        if (! $user->is_active) {
            return back()->withInput($request->only('email', 'remember'))
                ->withErrors(['email' => __('Your account has been suspended. Contact the Manager.')]);
        }

        // 4. Successful login
        RateLimiter::clear($throttleKey);
        if ($user->failed_login_lockouts > 0) {
            $user->update(['failed_login_lockouts' => 0]);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();
        DB::table('active_sessions')->updateOrInsert(
            ['session_id' => $request->session()->getId()],
            [
                'user_id' => $user->id,
                'last_seen_at' => now(),
                'ended_at' => null,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
