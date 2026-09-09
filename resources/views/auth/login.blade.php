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

        <!-- Credentials Error Banner -->
        @if ($errors->first('email') && $errors->first('email') !== __('Your account has been suspended. Contact the Manager.'))
            <div class="mb-5 flex items-center gap-2.5 rounded-2xl bg-rose-500/10 border border-rose-500/20 p-3.5 text-xs sm:text-sm font-medium text-rose-600 dark:text-rose-400">
                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>{{ $errors->first('email') }}</span>
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
                <div class="relative rounded-2xl border {{ $errors->has('email') ? 'border-rose-400 dark:border-rose-500/50 ring-2 ring-rose-500/10' : 'border-slate-200 dark:border-slate-700/80' }} bg-slate-50/50 dark:bg-slate-800/50 transition-all duration-200 focus-within:border-[var(--theme-primary)] focus-within:ring-4 focus-within:ring-[var(--theme-glow)] focus-within:bg-white dark:focus-within:bg-slate-800">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                        <svg class="h-4 w-4 sm:h-5 sm:w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                        </svg>
                    </div>
                    <input 
                        id="email" 
                        class="block w-full border-0 bg-transparent py-2.5 sm:py-3 pl-10 sm:pl-11 pr-4 text-xs sm:text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-0" 
                        type="email" 
                        name="email" 
                        value="{{ old('email') }}" 
                        required 
                        autofocus 
                        autocomplete="username" 
                        placeholder="exemple@gutcenter.com"
                    />
                </div>
                @if ($errors->first('email') && $errors->first('email') !== __('Your account has been suspended. Contact the Manager.'))
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
                        <a class="text-xs font-medium text-[var(--theme-primary)] hover:underline transition-colors focus:outline-none" href="{{ route('password.request') }}">
                            {{ __('Forgot your password?') }}
                        </a>
                    @endif
                </div>
                <div class="relative rounded-2xl border border-slate-200 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-800/50 transition-all duration-200 focus-within:border-[var(--theme-primary)] focus-within:ring-4 focus-within:ring-[var(--theme-glow)] focus-within:bg-white dark:focus-within:bg-slate-800">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                        <svg class="h-4 w-4 sm:h-5 sm:w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <input 
                        id="password" 
                        class="block w-full border-0 bg-transparent py-2.5 sm:py-3 pl-10 sm:pl-11 pr-11 text-xs sm:text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-0"
                        type="password"
                        name="password"
                        required 
                        autocomplete="current-password" 
                        placeholder="••••••••••••"
                    />
                    <!-- Show/Hide Password Toggle -->
                    <button 
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
                    <input id="remember_me" type="checkbox" class="h-4 w-4 rounded-md border-slate-300 dark:border-slate-700 text-[var(--theme-primary)] focus:ring-[var(--theme-glow)] bg-white dark:bg-slate-800 transition-colors" name="remember">
                    <span class="text-xs sm:text-sm font-medium text-slate-600 dark:text-slate-400 group-hover:text-slate-900 dark:group-hover:text-slate-200 transition-colors">{{ __('Remember me') }}</span>
                </label>
            </div>

            <!-- Action Button -->
            <div class="pt-2">
                <button 
                    id="submit-login-btn"
                    type="submit" 
                    class="shimmer-btn theme-btn-gradient w-full flex items-center justify-center gap-2 rounded-2xl py-3 px-5 text-sm sm:text-base font-bold text-white shadow-lg transition-all duration-200 hover:scale-[1.01] active:scale-[0.98] focus:outline-none"
                >
                    <span id="btn-text" class="flex items-center gap-2">
                        <span>{{ __('Log in') }}</span>
                        <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </span>
                    <span id="btn-spinner" class="hidden items-center gap-2">
                        <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>{{ __('Connexion en cours...') }}</span>
                    </span>
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

loginForm.addEventListener('submit', async () => {
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
    </script>

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
                    setTimeout(() => document.getElementById('login-pending-modal').remove(), 2500);
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
                    document.getElementById('login-pending-modal').remove();
                });
            }
        </script>
    @endif

    <!-- Suspended Account Modal -->
    @if ($errors->first('email') === __('Your account has been suspended. Contact the Manager.'))
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
