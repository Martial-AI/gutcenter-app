<x-guest-layout>
    <div class="relative overflow-hidden rounded-3xl bg-white/80 dark:bg-slate-900/80 backdrop-blur-2xl border border-white/60 dark:border-white/10 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.3)] dark:shadow-[0_25px_60px_-15px_rgba(0,0,0,0.6)] p-6 sm:p-9 transition-all duration-300">
        
        <!-- Top Colored Accent Bar -->
        <div class="absolute top-0 left-0 right-0 h-1 bg-[var(--theme-btn-gradient)]"></div>

        <!-- Header -->
        <div class="mb-7 text-center">
            <div class="inline-flex items-center gap-2 rounded-full bg-[var(--theme-primary)]/10 px-3 py-1 text-xs font-semibold text-[var(--theme-primary)] border border-[var(--theme-primary)]/20 mb-3">
                <span class="h-1.5 w-1.5 rounded-full bg-[var(--theme-primary)] animate-ping"></span>
                <span>{{ __('Portail Sécurisé') }}</span>
            </div>

            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                {{ __('Connexion') }}
            </h1>
            <p class="mt-1.5 text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                {{ __('Accédez à la gestion administrative et pédagogique') }}
            </p>
        </div>

        <!-- Session Status -->
        @if (session('status'))
            <div class="mb-5 flex items-center gap-2.5 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 p-3.5 text-xs sm:text-sm font-medium text-emerald-600 dark:text-emerald-400">
                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <!-- Notice : Lockout Wait Notice (visible when locked) -->
        <div id="lockout-wait-notice" class="hidden mb-5 items-center gap-2.5 rounded-2xl bg-amber-500/15 border border-amber-500/30 p-3.5 text-xs sm:text-sm font-semibold text-amber-600 dark:text-amber-400 animate-pulse">
            <svg class="h-4 w-4 shrink-0 text-amber-500 animate-spin" style="animation-duration: 3s;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ __('Veuillez patienter la fin du compte à rebours avant de pouvoir réessayer.') }}</span>
        </div>

        <!-- Credentials Error & Attempts Banner -->
        @if ($errors->first('email') && $errors->first('email') !== __('Your account has been suspended. Contact the Manager.') && !session('auto_suspended') && !session('admin_lockout'))
            <div id="error-banner-container" class="mb-5 space-y-2.5">
                <div class="flex items-center gap-2.5 rounded-2xl bg-rose-500/10 border border-rose-500/20 p-3.5 text-xs sm:text-sm font-medium text-rose-600 dark:text-rose-400">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>{{ $errors->first('email') }}</span>
                </div>

                <!-- Attempts Remaining Badge -->
                @if (session('attempts_left') !== null && session('attempts_left') > 0)
                    <div id="attempts-badge" class="flex items-center justify-between gap-2.5 rounded-2xl bg-amber-500/10 border border-amber-500/25 px-3.5 py-2.5 text-xs font-semibold text-amber-600 dark:text-amber-400">
                        <div class="flex items-center gap-2">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                            </span>
                            <span>{{ session('attempts_left') }} {{ __('tentative(s) restante(s) avant blocage temporaire') }}</span>
                        </div>
                        <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-700 dark:text-amber-300 text-xs font-bold font-mono">
                            {{ session('attempts_left') }}/5
                        </span>
                    </div>
                @endif
            </div>
        @endif

        <form id="login-form" method="POST" action="{{ route('login') }}" class="space-y-4 sm:space-y-5">
            @csrf
            <input id="device-model" type="hidden" name="device_model">
            <input id="device-platform" type="hidden" name="device_platform">
            <input id="device-browser" type="hidden" name="device_browser">

            <!-- Email Address Field -->
            <div>
                <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                    {{ __('Email') }}
                </label>
                <div id="email-field-wrapper" class="relative rounded-2xl border {{ $errors->has('email') ? 'border-rose-400 dark:border-rose-500/50 ring-2 ring-rose-500/10' : 'border-slate-200 dark:border-slate-700/80' }} bg-slate-50/50 dark:bg-slate-800/50 transition-all duration-200 focus-within:border-[var(--theme-primary)] focus-within:ring-4 focus-within:ring-[var(--theme-glow)] focus-within:bg-white dark:focus-within:bg-slate-800">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                        <svg class="h-4 w-4 sm:h-5 sm:w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                        </svg>
                    </div>
                    <input 
                        id="email" 
                        class="block w-full border-0 bg-transparent py-2.5 sm:py-3 pl-10 sm:pl-11 pr-4 text-xs sm:text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-0 disabled:opacity-50 disabled:cursor-not-allowed" 
                        type="email" 
                        name="email" 
                        value="{{ old('email') }}" 
                        required 
                        autofocus 
                        autocomplete="username" 
                        placeholder="exemple@gutcenter.com"
                    />
                </div>
                @if ($errors->first('email') && $errors->first('email') !== __('Your account has been suspended. Contact the Manager.') && !session('auto_suspended') && !session('admin_lockout'))
                    <div class="mt-1.5 flex items-center gap-1.5 text-xs text-rose-500">
                        <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>{{ $errors->first('email') }}</span>
                    </div>
                @endif
            </div>

            <!-- Password Field -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                        {{ __('Password') }}
                    </label>
                    @if (Route::has('password.request'))
                        <a id="forgot-password-link" class="text-xs font-medium text-[var(--theme-primary)] hover:underline transition-colors focus:outline-none" href="{{ route('password.request') }}">
                            {{ __('Forgot your password?') }}
                        </a>
                    @endif
                </div>
                <div id="password-field-wrapper" class="relative rounded-2xl border border-slate-200 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-800/50 transition-all duration-200 focus-within:border-[var(--theme-primary)] focus-within:ring-4 focus-within:ring-[var(--theme-glow)] focus-within:bg-white dark:focus-within:bg-slate-800">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                        <svg class="h-4 w-4 sm:h-5 sm:w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <input 
                        id="password" 
                        class="block w-full border-0 bg-transparent py-2.5 sm:py-3 pl-10 sm:pl-11 pr-11 text-xs sm:text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-0 disabled:opacity-50 disabled:cursor-not-allowed"
                        type="password"
                        name="password"
                        required 
                        autocomplete="current-password" 
                        placeholder="••••••••••••"
                    />
                    <!-- Show/Hide Password Toggle -->
                    <button 
                        id="toggle-pwd-btn"
                        type="button" 
                        onclick="togglePasswordVisibility('password', this)" 
                        class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 focus:outline-none"
                        title="{{ __('Show password') }}"
                    >
                        <svg class="h-4 w-4 sm:h-5 sm:w-5 eye-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg class="h-4 w-4 sm:h-5 sm:w-5 eye-slash-icon hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                        </svg>
                    </button>
                </div>
                @if ($errors->first('password'))
                    <div class="mt-1.5 flex items-center gap-1.5 text-xs text-rose-500">
                        <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>{{ $errors->first('password') }}</span>
                    </div>
                @endif
            </div>

            <!-- Remember Me Switch -->
            <div class="flex items-center justify-between pt-1">
                <label for="remember_me" class="inline-flex items-center gap-2.5 cursor-pointer select-none group">
                    <input id="remember_me" type="checkbox" class="h-4 w-4 rounded-md border-slate-300 dark:border-slate-700 text-[var(--theme-primary)] focus:ring-[var(--theme-glow)] bg-white dark:bg-slate-800 transition-colors disabled:opacity-50 disabled:cursor-not-allowed" name="remember">
                    <span class="text-xs sm:text-sm font-medium text-slate-600 dark:text-slate-400 group-hover:text-slate-900 dark:group-hover:text-slate-200 transition-colors">{{ __('Remember me') }}</span>
                </label>
            </div>

            <!-- Action Button / Real-Time Countdown Screen -->
            <div class="pt-2 relative">
                <button 
                    id="submit-login-btn"
                    type="submit" 
                    class="shimmer-btn theme-btn-gradient w-full flex items-center justify-center gap-2 rounded-2xl py-3 px-5 text-sm sm:text-base font-bold text-white shadow-lg transition-all duration-300 hover:scale-[1.01] active:scale-[0.98] focus:outline-none select-none"
                >
                    <!-- Normal State -->
                    <span id="btn-text" class="flex items-center gap-2">
                        <span>{{ __('Log in') }}</span>
                        <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </span>

                    <!-- Loading Spinner -->
                    <span id="btn-spinner" class="hidden items-center gap-2">
                        <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>{{ __('Connexion en cours...') }}</span>
                    </span>

                    <!-- Real-Time Countdown on Button -->
                    <div id="btn-countdown" class="hidden items-center justify-center gap-3 w-full">
                        <div class="relative flex items-center justify-center shrink-0">
                            <svg class="w-8 h-8 text-amber-500 -rotate-90" viewBox="0 0 36 36">
                                <path class="text-white/20" stroke-width="3" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                <path id="countdown-progress-bar" class="text-amber-300 transition-all duration-1000 ease-linear" stroke-dasharray="100, 100" stroke-linecap="round" stroke-width="3" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            </svg>
                            <span id="countdown-sec-num" class="absolute text-[11px] font-black text-amber-200 font-mono">60</span>
                        </div>
                        <div class="text-left leading-tight">
                            <div class="text-[11px] font-extrabold uppercase tracking-wider text-amber-200 flex items-center gap-1.5">
                                <span class="inline-block h-1.5 w-1.5 rounded-full bg-amber-400 animate-ping"></span>
                                <span>{{ __('Connexion verrouillée') }}</span>
                            </div>
                            <div class="text-xs text-white/95 font-medium mt-0.5">
                                <span>{{ __('Compte à rebours :') }}</span> <span id="countdown-sec-text" class="font-extrabold text-amber-200 font-mono text-sm">60s</span>
                            </div>
                        </div>
                    </div>
                </button>
            </div>
        </form>

        <!-- Extra Bottom Security info -->
        <div class="mt-6 pt-5 border-t border-slate-200/60 dark:border-slate-800/80 flex items-center justify-center gap-2 text-[11px] text-slate-400 dark:text-slate-500">
            <svg class="h-3.5 w-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            <span>Connexion SSL 256-bit • Protection des données scolaires</span>
        </div>
    </div>

    <!-- Password visibility toggle script -->
    <script>
        function togglePasswordVisibility(inputId, btn) {
            const input = document.getElementById(inputId);
            const eyeIcon = btn.querySelector('.eye-icon');
            const eyeSlashIcon = btn.querySelector('.eye-slash-icon');
            if (input.type === 'password') {
                input.type = 'text';
                eyeIcon.classList.add('hidden');
                eyeSlashIcon.classList.remove('hidden');
                btn.title = '{{ __('Hide password') }}';
            } else {
                input.type = 'password';
                eyeIcon.classList.remove('hidden');
                eyeSlashIcon.classList.add('hidden');
                btn.title = '{{ __('Show password') }}';
            }
        }

        // Handle device platform / browser resolution & submit feedback
        const loginForm = document.getElementById('login-form');
        const submitBtn = document.getElementById('submit-login-btn');
        const btnText = document.getElementById('btn-text');
        const btnSpinner = document.getElementById('btn-spinner');

        loginForm.addEventListener('submit', async (e) => {
            // If currently counting down / locked, strictly block submission
            const storedUntil = parseInt(localStorage.getItem('gut_login_locked_until') || '0', 10);
            if (storedUntil > Date.now() || submitBtn.disabled) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }

            // Visual feedback
            submitBtn.disabled = true;
            btnText.classList.add('hidden');
            btnSpinner.classList.remove('hidden');
            btnSpinner.classList.add('flex');

            let platform = navigator.userAgentData?.platform || navigator.platform || '';
            let model = '';
            let browser = '';

            try {
                if (navigator.userAgentData?.getHighEntropyValues) {
                    const details = await navigator.userAgentData.getHighEntropyValues([
                        'model',
                        'platform'
                    ]);

                    model = details.model || '';
                    platform = details.platform || platform;

                    const brands = (navigator.userAgentData.brands || [])
                        .map(item => item.brand);

                    browser =
                        brands.find(brand =>
                            /Microsoft Edge|Google Chrome|Opera|Samsung Internet|Firefox/i.test(brand)
                        ) ||
                        brands.find(brand =>
                            !/not.*brand|chromium/i.test(brand)
                        ) ||
                        '';
                }
            } catch (error) {
                // Device information is optional; never block login.
            }

            document.getElementById('device-model').value = model;
            document.getElementById('device-platform').value = platform;
            document.getElementById('device-browser').value = browser;
        });

        // Real-Time Countdown & Blur Handler (Persistent across page reloads F5)
        (function() {
            const serverLockedUntil = {{ (isset($lockedUntil) && $lockedUntil) ? (int) $lockedUntil : (session('locked_until') ? (int) session('locked_until') : 'null') }};
            const serverLockoutSeconds = {{ (isset($lockoutSeconds) && $lockoutSeconds) ? (int) $lockoutSeconds : (session('lockout_seconds') ? (int) session('lockout_seconds') : 'null') }};

            // If server signaled lockout, persist in localStorage
            if (serverLockedUntil && serverLockedUntil > Date.now()) {
                localStorage.setItem('gut_login_locked_until', serverLockedUntil);
                localStorage.setItem('gut_login_lockout_total', serverLockoutSeconds || 60);
            }

            const emailInput = document.getElementById('email');
            const pwdInput = document.getElementById('password');
            const rememberMe = document.getElementById('remember_me');
            const btnCountdown = document.getElementById('btn-countdown');
            const btnTextEl = document.getElementById('btn-text');
            const btnSpinnerEl = document.getElementById('btn-spinner');
            const countdownSecNum = document.getElementById('countdown-sec-num');
            const countdownSecText = document.getElementById('countdown-sec-text');
            const progressBar = document.getElementById('countdown-progress-bar');
            const attemptsBadge = document.getElementById('attempts-badge');
            const lockoutNotice = document.getElementById('lockout-wait-notice');

            let countdownTimer = null;

            function activateLockout(storedLockedUntil, totalDuration) {
                // 1. Make button non-clickable, disabled (clean, crisp, no blur)
                submitBtn.disabled = true;
                submitBtn.style.filter = 'none';
                submitBtn.style.opacity = '1';
                submitBtn.style.pointerEvents = 'none';
                submitBtn.style.cursor = 'not-allowed';
                submitBtn.classList.remove('theme-btn-gradient', 'hover:scale-[1.01]', 'active:scale-[0.98]');
                submitBtn.classList.add('bg-slate-900', 'border-2', 'border-amber-500/60', 'shadow-lg', 'shadow-amber-500/10');

                // 2. Lock inputs so user can literally do nothing but wait
                if (emailInput) { emailInput.disabled = true; emailInput.readOnly = true; }
                if (pwdInput) { pwdInput.disabled = true; pwdInput.readOnly = true; }
                if (rememberMe) { rememberMe.disabled = true; }

                // 3. Show countdown display on button
                btnTextEl.classList.add('hidden');
                btnSpinnerEl.classList.add('hidden');
                btnCountdown.classList.remove('hidden');
                btnCountdown.classList.add('flex');

                if (attemptsBadge) attemptsBadge.classList.add('hidden');
                if (lockoutNotice) {
                    lockoutNotice.classList.remove('hidden');
                    lockoutNotice.classList.add('flex');
                }

                function tick() {
                    const now = Date.now();
                    const remainingMs = storedLockedUntil - now;
                    const remainingSec = Math.max(0, Math.ceil(remainingMs / 1000));

                    if (countdownSecNum) countdownSecNum.textContent = remainingSec;
                    if (countdownSecText) countdownSecText.textContent = remainingSec + 's';

                    if (progressBar && totalDuration > 0) {
                        const progress = Math.max(0, Math.min(100, (remainingSec / totalDuration) * 100));
                        progressBar.setAttribute('stroke-dasharray', `${progress}, 100`);
                    }

                    if (remainingSec <= 0) {
                        clearInterval(countdownTimer);
                        localStorage.removeItem('gut_login_locked_until');
                        localStorage.removeItem('gut_login_lockout_total');

                        // Restore form & button state completely
                        submitBtn.disabled = false;
                        submitBtn.style.filter = 'none';
                        submitBtn.style.opacity = '1';
                        submitBtn.style.pointerEvents = 'auto';
                        submitBtn.style.cursor = 'pointer';
                        submitBtn.classList.remove('bg-slate-900', 'border-2', 'border-amber-500/60', 'shadow-lg', 'shadow-amber-500/10');
                        submitBtn.classList.add('theme-btn-gradient', 'hover:scale-[1.01]', 'active:scale-[0.98]');

                        btnCountdown.classList.add('hidden');
                        btnCountdown.classList.remove('flex');
                        btnTextEl.classList.remove('hidden');

                        if (emailInput) { emailInput.disabled = false; emailInput.readOnly = false; emailInput.focus(); }
                        if (pwdInput) { pwdInput.disabled = false; pwdInput.readOnly = false; }
                        if (rememberMe) { rememberMe.disabled = false; }
                        if (lockoutNotice) lockoutNotice.classList.add('hidden');
                    }
                }

                tick();
                countdownTimer = setInterval(tick, 1000);
            }

            // Check upon load
            const storedUntil = parseInt(localStorage.getItem('gut_login_locked_until') || '0', 10);
            const totalDuration = parseInt(localStorage.getItem('gut_login_lockout_total') || '60', 10);

            if (storedUntil && storedUntil > Date.now()) {
                activateLockout(storedUntil, totalDuration);
            } else {
                localStorage.removeItem('gut_login_locked_until');
                localStorage.removeItem('gut_login_lockout_total');
            }
        })();
    </script>

    <!-- Auto-Suspended Account Modal (after 2 lockouts) -->
    @if (session('auto_suspended'))
        <div id="auto-suspended-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/75 backdrop-blur-md p-4 animate-fade-in">
            <div class="w-full max-w-sm rounded-3xl bg-white dark:bg-slate-900 p-6 sm:p-7 text-center shadow-2xl border border-rose-500/30">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-rose-500/15 text-rose-500 mb-4 ring-8 ring-rose-500/10">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <h2 class="text-lg sm:text-xl font-extrabold text-rose-600 dark:text-rose-400">
                    {{ __('Compte suspendu automatiquement') }}
                </h2>
                <p class="mt-2.5 text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                    {{ __("Votre compte a été suspendu automatiquement après plusieurs tentatives échouées. Veuillez contacter l'administrateur pour réactiver votre accès.") }}
                </p>
                <div class="mt-6">
                    <button 
                        type="button" 
                        onclick="document.getElementById('auto-suspended-modal').remove()" 
                        class="w-full rounded-xl bg-gradient-to-r from-rose-600 to-red-600 py-3 text-xs sm:text-sm font-bold text-white shadow-lg hover:shadow-rose-500/25 transition-all active:scale-[0.98]"
                    >
                        {{ __('Compris') }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Admin Lockout / Password Reset Modal -->
    @if (session('admin_lockout'))
        <div id="admin-lockout-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/75 backdrop-blur-md p-4 animate-fade-in">
            <div class="w-full max-w-sm sm:max-w-md rounded-3xl bg-white dark:bg-slate-900 p-6 sm:p-7 text-center shadow-2xl border border-amber-500/30">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-amber-500/15 text-amber-500 mb-4 ring-8 ring-amber-500/10">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                </div>
                <h2 class="text-lg sm:text-xl font-extrabold text-amber-600 dark:text-amber-400">
                    {{ __('Sécurité Administrateur') }}
                </h2>
                <p class="mt-2.5 text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                    {{ __('Trop de tentatives de connexion sur ce compte administrateur. Pour des raisons de sécurité, veuillez réinitialiser votre mot de passe.') }}
                </p>
                <div class="mt-6 flex flex-col gap-2.5">
                    @if (Route::has('password.request'))
                        <a 
                            href="{{ route('password.request') }}" 
                            class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 py-3 px-4 text-xs sm:text-sm font-bold text-white shadow-lg hover:shadow-amber-500/25 transition-all hover:scale-[1.01] active:scale-[0.98]"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span>{{ __('Réinitialiser mon mot de passe') }}</span>
                        </a>
                    @endif
                    <button 
                        type="button" 
                        onclick="document.getElementById('admin-lockout-modal').remove()" 
                        class="w-full rounded-xl border border-slate-300 dark:border-slate-700 py-2.5 text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                    >
                        {{ __('Fermer') }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Pending Login Approval Modal -->
    @if(session('pending_login_approval'))
        <div id="login-pending-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 backdrop-blur-md p-4">
            <div class="w-full max-w-sm rounded-3xl bg-white dark:bg-slate-900 p-6 text-center shadow-2xl border border-amber-500/20">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-500/10 text-2xl text-amber-500 mb-4">
                    ⏳
                </div>
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">{{ __('Connection request pending') }}</h2>
                <p id="login-pending-message" class="mt-2 text-xs sm:text-sm text-slate-600 dark:text-slate-400">
                    {{ __('This account is currently connected on another device. Waiting for approval from that device.') }}
                </p>
                <div class="mt-5 flex justify-center gap-1.5">
                    <span class="h-2.5 w-2.5 animate-bounce rounded-full bg-[var(--theme-primary)]"></span>
                    <span class="h-2.5 w-2.5 animate-bounce rounded-full bg-[var(--theme-primary)] [animation-delay:150ms]"></span>
                    <span class="h-2.5 w-2.5 animate-bounce rounded-full bg-[var(--theme-primary)] [animation-delay:300ms]"></span>
                </div>
                <button id="cancel-pending-approval-btn" type="button" class="mt-6 w-full rounded-xl border border-slate-300 dark:border-slate-700 px-4 py-2.5 text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    {{ __('Cancel') }}
                </button>
            </div>
        </div>

        <script>
            const pendingCheck = setInterval(async () => {
                const response = await fetch('{{ route('login-approval.status') }}', { headers: { Accept: 'application/json' } });
                const data = await response.json();
                if (data.status === 'approved') {
                    clearInterval(pendingCheck);
                    window.location = data.redirect;
                    return;
                }
                if (data.status === 'declined' || data.status === 'expired') {
                    clearInterval(pendingCheck);
                    document.getElementById('login-pending-message').textContent = data.status === 'declined' 
                        ? '{{ __('Connection refused by the connected device.') }}' 
                        : '{{ __('The connection request has expired. Please try again.') }}';
                    setTimeout(() => document.getElementById('login-pending-modal')?.remove(), 2500);
                }
            }, 3000);

            const cancelBtn = document.getElementById('cancel-pending-approval-btn');
            if (cancelBtn) {
                cancelBtn.addEventListener('click', async () => {
                    await fetch('{{ route('login-approval.cancel', session('pending_login_approval')) }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            Accept: 'application/json'
                        }
                    });
                    clearInterval(pendingCheck);
                    document.getElementById('login-pending-modal')?.remove();
                });
            }
        </script>
    @endif

    <!-- Suspended Account Modal (Manual or Existing Suspension) -->
    @if ($errors->first('email') === __('Your account has been suspended. Contact the Manager.') && !session('auto_suspended'))
        <div id="suspended-account-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 backdrop-blur-md p-4">
            <div class="w-full max-w-sm rounded-3xl bg-white dark:bg-slate-900 p-6 text-center shadow-2xl border border-rose-500/20">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-rose-500/10 text-rose-500 mb-4">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <h2 class="text-lg font-bold text-rose-600 dark:text-rose-400">{{ __('Account suspended') }}</h2>
                <p class="mt-2 text-xs sm:text-sm text-slate-600 dark:text-slate-300">{{ __('Your account has been suspended. Contact the Manager.') }}</p>
                <button type="button" onclick="document.getElementById('suspended-account-modal').remove()" class="mt-6 w-full rounded-xl bg-gradient-to-r from-rose-600 to-red-600 py-2.5 text-xs sm:text-sm font-semibold text-white shadow-md hover:shadow-lg transition-all">{{ __('OK') }}</button>
            </div>
        </div>
    @endif
</x-guest-layout>
