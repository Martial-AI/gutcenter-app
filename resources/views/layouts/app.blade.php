<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>
        <link rel="icon" type="image/png" href="{{ asset('images/gut-logo.png') }}">
        <style>
            #activity-badge { top: -0.35rem; right: -0.35rem; z-index: 10; pointer-events: none; }
            button:has(#activity-badge) svg { position: relative; z-index: 0; }
            #activity-history-modal { z-index: 100; align-items: flex-start; overflow-y: auto; padding: 5.5rem 1rem 1rem; }
            #activity-history-modal > div { max-height: calc(100vh - 6.5rem); }
            @media (min-width: 640px) { #activity-history-modal { align-items: center; padding: 1.5rem; } #activity-history-modal > div { max-height: 85vh; } }
        </style>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800|outfit:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Theme Pre-Initialization (Prevents FOUC) -->
        <script>
            (function() {
                const savedMode = localStorage.getItem('gut_auth_mode');
                const isDark = savedMode === 'dark' || (!savedMode && window.matchMedia('(prefers-color-scheme: dark)').matches);
                if (isDark) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }

                const savedTheme = localStorage.getItem('gut_auth_theme') || 'emerald';
                document.documentElement.setAttribute('data-theme', savedTheme);

                const themes = {
                    emerald: { primary: '#059669', hover: '#047857', secondary: '#0d9488', accent: '#10b981', glow: 'rgba(16, 185, 129, 0.4)', gradient: 'linear-gradient(135deg, #059669 0%, #0d9488 60%, #047857 100%)', btnGradient: 'linear-gradient(135deg, #059669 0%, #0d9488 100%)', badgeBg: isDark ? 'rgba(16, 185, 129, 0.18)' : '#ecfdf5', badgeText: isDark ? '#6ee7b7' : '#065f46', badgeBorder: isDark ? 'rgba(16, 185, 129, 0.35)' : '#a7f3d0', bgGlow: isDark ? 'rgba(16, 185, 129, 0.12)' : 'rgba(16, 185, 129, 0.18)' },
                    ocean: { primary: '#2563eb', hover: '#1d4ed8', secondary: '#0284c7', accent: '#38bdf8', glow: 'rgba(37, 99, 235, 0.4)', gradient: 'linear-gradient(135deg, #2563eb 0%, #0284c7 60%, #1d4ed8 100%)', btnGradient: 'linear-gradient(135deg, #2563eb 0%, #0284c7 100%)', badgeBg: isDark ? 'rgba(37, 99, 235, 0.18)' : '#eff6ff', badgeText: isDark ? '#93c5fd' : '#1e40af', badgeBorder: isDark ? 'rgba(37, 99, 235, 0.35)' : '#bfdbfe', bgGlow: isDark ? 'rgba(37, 99, 235, 0.12)' : 'rgba(37, 99, 235, 0.18)' },
                    amethyst: { primary: '#7c3aed', hover: '#6d28d9', secondary: '#9333ea', accent: '#c084fc', glow: 'rgba(124, 58, 237, 0.4)', gradient: 'linear-gradient(135deg, #7c3aed 0%, #9333ea 60%, #6d28d9 100%)', btnGradient: 'linear-gradient(135deg, #7c3aed 0%, #9333ea 100%)', badgeBg: isDark ? 'rgba(124, 58, 237, 0.18)' : '#faf5ff', badgeText: isDark ? '#d8b4fe' : '#5b21b6', badgeBorder: isDark ? 'rgba(124, 58, 237, 0.35)' : '#e9d5ff', bgGlow: isDark ? 'rgba(124, 58, 237, 0.12)' : 'rgba(124, 58, 237, 0.18)' },
                    sunset: { primary: '#ea580c', hover: '#c2410c', secondary: '#e11d48', accent: '#fb923c', glow: 'rgba(234, 88, 12, 0.4)', gradient: 'linear-gradient(135deg, #ea580c 0%, #e11d48 60%, #c2410c 100%)', btnGradient: 'linear-gradient(135deg, #ea580c 0%, #e11d48 100%)', badgeBg: isDark ? 'rgba(234, 88, 12, 0.18)' : '#fff7ed', badgeText: isDark ? '#fed7aa' : '#9a3412', badgeBorder: isDark ? 'rgba(234, 88, 12, 0.35)' : '#fed7aa', bgGlow: isDark ? 'rgba(234, 88, 12, 0.12)' : 'rgba(234, 88, 12, 0.18)' },
                    cyber: { primary: '#0891b2', hover: '#0e7490', secondary: '#059669', accent: '#06b6d4', glow: 'rgba(6, 182, 212, 0.4)', gradient: 'linear-gradient(135deg, #0891b2 0%, #059669 60%, #0e7490 100%)', btnGradient: 'linear-gradient(135deg, #0891b2 0%, #059669 100%)', badgeBg: isDark ? 'rgba(6, 182, 212, 0.18)' : '#ecfeff', badgeText: isDark ? '#a5f3fc' : '#155e75', badgeBorder: isDark ? 'rgba(6, 182, 212, 0.35)' : '#a5f3fc', bgGlow: isDark ? 'rgba(6, 182, 212, 0.12)' : 'rgba(6, 182, 212, 0.18)' }
                };
                const t = themes[savedTheme] || themes.emerald;
                const r = document.documentElement;
                r.style.setProperty('--theme-primary', t.primary);
                r.style.setProperty('--theme-primary-hover', t.hover);
                r.style.setProperty('--theme-secondary', t.secondary);
                r.style.setProperty('--theme-accent', t.accent);
                r.style.setProperty('--theme-glow', t.glow);
                r.style.setProperty('--theme-gradient', t.gradient);
                r.style.setProperty('--theme-btn-gradient', t.btnGradient);
                r.style.setProperty('--theme-badge-bg', t.badgeBg);
                r.style.setProperty('--theme-badge-text', t.badgeText);
                r.style.setProperty('--theme-badge-border', t.badgeBorder);
                r.style.setProperty('--theme-bg-glow', t.bgGlow);
            })();
        </script>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased transition-colors duration-300">
        <div id="app-main-wrapper" class="min-h-screen transition-colors duration-300">
            @include('layouts.navigation')
            @php($serverLoginApproval = auth()->check() ? \App\Models\LoginApproval::where('user_id', auth()->id())->where('status', 'pending')->where('expires_at', '>', now())->latest()->first() : null)
            @if($serverLoginApproval)
                <div id="server-device-login-approval" style="position:fixed;inset:0;z-index:10000;display:flex;align-items:center;justify-content:center;background:rgb(0 0 0 / .4);padding:1rem">
                    <div class="w-full max-w-sm rounded-2xl bg-white p-6 text-center shadow-2xl"><div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-amber-100 text-xl text-amber-700">!</div><h2 class="mt-4 text-lg font-semibold text-slate-900">{{ __('New connection request') }}</h2><p class="mt-2 text-sm text-slate-600">{{ __('Another device wants to connect to this account. Accepting will sign you out of this device.') }}</p><div class="mt-4 rounded-xl bg-slate-50 px-4 py-3 text-left"><p class="font-semibold text-slate-800">{{ $serverLoginApproval->requester_device_name }}</p><p class="mt-0.5 text-xs text-slate-500">{{ $serverLoginApproval->requester_device_platform }} : {{ $serverLoginApproval->requester_ip_address ?: '—' }}</p></div><div class="mt-6 flex justify-center gap-3"><button type="button" data-server-decision="declined" class="rounded-lg border px-4 py-2">{{ __('Refuse') }}</button><button type="button" data-server-decision="approved" class="rounded-lg bg-emerald-700 px-4 py-2 text-white">{{ __('Accept') }}</button></div></div>
                </div>
                <script>document.querySelectorAll('[data-server-decision]').forEach(button=>button.addEventListener('click',async()=>{const response=await fetch('{{ route('login-approval.decide', $serverLoginApproval) }}',{method:'POST',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Content-Type':'application/json',Accept:'application/json'},body:JSON.stringify({decision:button.dataset.serverDecision})});const result=await response.json();if(result.status==='approved')window.location=result.redirect;else document.getElementById('server-device-login-approval').remove()}));</script>
            @endif

            <!-- Page Heading -->
            @isset($header)
                <header class="sticky top-[65px] z-30 border-b border-slate-200/50 bg-white/70 shadow-sm backdrop-blur-md transition-all">
                    <div class="max-w-7xl mx-auto py-5 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>

            @if (session('success'))
                <div id="success-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
                    <div class="w-full max-w-sm rounded-xl bg-white p-6 text-center shadow-xl">
                        <h3 class="text-lg font-semibold text-emerald-800">{{ __('Action successful') }}</h3>
                        <p class="mt-2 text-gray-700">{{ session('success') }}</p>
                        <button type="button" onclick="document.getElementById('success-modal').remove()" class="mt-5 rounded-lg bg-emerald-700 px-5 py-2 text-white">{{ __('OK') }}</button>
                    </div>
                </div>
            @endif

            @if (session('error') || $errors->any())
                <div id="error-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
                    <div class="w-full max-w-sm rounded-xl bg-white p-6 text-center shadow-xl">
                        <h3 class="text-lg font-semibold text-red-800">{{ __('Action not completed') }}</h3>
                        <p class="mt-2 text-gray-700">{{ session('error') ?: $errors->first() }}</p>
                        <button type="button" onclick="document.getElementById('error-modal').remove()" class="mt-5 rounded-lg bg-gray-700 px-5 py-2 text-white">{{ __('OK') }}</button>
                    </div>
                </div>
            @endif
        </div>
        <script>
            document.querySelectorAll('input[type="password"]').forEach(function (input) {
                const parent = input.parentElement;
                parent.classList.add('relative');
                input.classList.add('pr-20');
                const button = document.createElement('button');
                button.type = 'button';
                button.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 12S5.25 5.25 12 5.25 21.75 12 21.75 12 18.75 18.75 12 18.75 2.25 12 2.25 12Z"/><circle cx="12" cy="12" r="3" stroke-width="2"/></svg>';
                button.title = @json(__('Show password'));
                button.setAttribute('aria-label', @json(__('Show password')));
                button.className = 'absolute right-2 top-1/2 -translate-y-1/2 text-emerald-700';
                button.addEventListener('click', function () {
                    const visible = input.type === 'text';
                    input.type = visible ? 'password' : 'text';
                    button.title = visible ? @json(__('Show password')) : @json(__('Hide password'));
                    button.setAttribute('aria-label', button.title);
                });
                parent.appendChild(button);
            });
        </script>
        <script>
            let applicationDataVersion = null;
            window.applicationNavigating = false;
            let formHasUnsavedChanges = false;
            document.addEventListener('click', event => { if (event.target.closest('a')) window.applicationNavigating = true; });
            document.addEventListener('submit', () => { window.applicationNavigating = true; });
            document.querySelectorAll('form').forEach(form => form.addEventListener('input', () => { formHasUnsavedChanges = true; }, {once: true}));
            document.querySelectorAll('form').forEach(form => form.addEventListener('submit', () => { formHasUnsavedChanges = false; }));
            function hasOpenModal() { return [...document.querySelectorAll('[id$="modal"]')].some(element => !element.classList.contains('hidden')); }
            async function synchronizeApplicationData() {
                try {
                    const response = await fetch('{{ route('data-sync.version') }}', {headers: {Accept: 'application/json'}, cache: 'no-store'});
                    if (!response.ok) return;
                    const data = await response.json();
                    if (!applicationDataVersion) { applicationDataVersion = data.version; return; }
                    if (applicationDataVersion !== data.version) {
                        applicationDataVersion = data.version;
                        if (!formHasUnsavedChanges && !hasOpenModal()) { window.applicationNavigating = true; window.location.reload(); }
                    }
                } catch (error) {}
            }
            synchronizeApplicationData();
            setInterval(synchronizeApplicationData, 5000);
        </script>
        <script>
            function sessionHeartbeat() {
                fetch('{{ route('session-heartbeat.ping') }}', {method: 'POST', headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, Accept: 'application/json'}}).catch(() => {});
            }
            sessionHeartbeat();
            setInterval(sessionHeartbeat, 5000);
        </script>
        <script>
            let activeApprovalId = null;
            async function checkLoginApproval() {
                try {
                    const response = await fetch('{{ route('login-approval.active') }}', {headers: {Accept: 'application/json'}});
                    if (!response.ok) return;
                    const data = await response.json();
                    if (!data.pending) { document.getElementById('device-login-approval')?.remove(); activeApprovalId = null; return; }
                    if (activeApprovalId === data.approval_id) return;
                    activeApprovalId = data.approval_id;
                    const modal = document.createElement('div');
                    modal.id = 'device-login-approval';
                    modal.className = 'fixed inset-0 z-[80] flex items-center justify-center bg-black/40 p-4';
                    modal.style.cssText = 'position:fixed;inset:0;z-index:10000;display:flex;align-items:center;justify-content:center;';
                    modal.innerHTML = `<div class="w-full max-w-sm rounded-2xl bg-white p-6 text-center shadow-xl"><div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-amber-100 text-xl text-amber-700">!</div><h2 class="mt-4 text-lg font-semibold text-gray-900">{{ __('New connection request') }}</h2><p class="mt-2 text-sm text-gray-600">{{ __('Another device wants to connect to this account. Accepting will sign you out of this device.') }}</p><div class="mt-4 rounded-xl bg-slate-50 px-4 py-3 text-left"><p data-requester-device class="font-semibold text-slate-800"></p><p data-requester-ip class="mt-0.5 text-xs text-slate-500"></p></div><div class="mt-6 flex justify-center gap-3"><button data-decision="declined" class="rounded-lg border px-4 py-2">{{ __('Refuse') }}</button><button data-decision="approved" class="rounded-lg bg-emerald-700 px-4 py-2 text-white">{{ __('Accept') }}</button></div></div>`;
                    document.body.appendChild(modal);
                    modal.querySelector('[data-requester-device]').textContent = data.requester_device || '{{ __('Unknown device') }}';
                    modal.querySelector('[data-requester-ip]').textContent = `${data.requester_platform || '{{ __('Device') }}'} : ${data.requester_ip || '—'}`;
                    modal.querySelectorAll('[data-decision]').forEach(button => button.addEventListener('click', async () => {
                        const response = await fetch(`/login-approval/${data.approval_id}/decision`, {method: 'POST', headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, Accept: 'application/json', 'Content-Type': 'application/json'}, body: JSON.stringify({decision: button.dataset.decision})});
                        const result = await response.json();
                        if (result.status === 'approved') window.location = result.redirect; else { modal.remove(); activeApprovalId = null; }
                    }));
                } catch (error) {}
            }
            checkLoginApproval();
            setInterval(checkLoginApproval, 3000);
        </script>
        <script>
            window.confirmDeletion = message => new Promise(resolve => {
                const modal = document.createElement('div');
                modal.className = 'fixed inset-0 z-[120] flex items-center justify-center bg-black/40 p-4';
                modal.style.cssText = 'position:fixed;inset:0;z-index:9999;display:flex;align-items:center;justify-content:center;';
                modal.innerHTML = `<div class="w-full max-w-sm rounded-2xl bg-white p-6 text-center shadow-2xl"><div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-100 text-xl text-red-700">!</div><h2 class="mt-4 text-lg font-semibold text-slate-900">{{ __('Confirm deletion') }}</h2><p class="mt-2 text-sm text-slate-600"></p><div class="mt-6 flex justify-center gap-3"><button type="button" data-cancel class="rounded-lg border border-slate-300 px-4 py-2 text-sm">{{ __('Cancel') }}</button><button type="button" data-confirm class="rounded-lg bg-red-600 px-4 py-2 text-sm text-white">{{ __('Confirm') }}</button></div></div>`;
                modal.querySelector('p').textContent = message;
                modal.querySelector('[data-cancel]').onclick = () => { modal.remove(); resolve(false); };
                modal.querySelector('[data-confirm]').onclick = () => { modal.remove(); resolve(true); };
                document.body.appendChild(modal);
            });
        </script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                document.querySelectorAll('p').forEach((amount) => {
                    if (!/\bAr\s*$/.test(amount.textContent.trim()) || amount.dataset.moneyProtected) return;
                    amount.dataset.moneyProtected = 'true';
                    amount.dataset.moneyValue = amount.textContent.trim();
                    amount.classList.add('cursor-pointer', 'select-none');
                    amount.textContent = '***** Ar';
                    amount.dataset.hidden = 'true';
                    amount.title = '{{ __('Show amount') }}';
                    amount.addEventListener('click', () => {
                        const hidden = amount.dataset.hidden === 'true';
                        amount.textContent = hidden ? amount.dataset.moneyValue : '***** Ar';
                        amount.dataset.hidden = hidden ? 'false' : 'true';
                        amount.title = hidden ? '{{ __('Hide amount') }}' : '{{ __('Show amount') }}';
                    });
                });
            });
        </script>
        <script>
            // App Theme and Dark Mode Controller
            const appThemeConfig = {
                emerald: {
                    name: 'Émeraude',
                    gradientClass: 'bg-gradient-to-tr from-emerald-600 to-teal-400',
                    primary: '#059669',
                    hover: '#047857',
                    secondary: '#0d9488',
                    accent: '#10b981',
                    glow: 'rgba(16, 185, 129, 0.45)',
                    gradient: 'linear-gradient(135deg, #059669 0%, #0d9488 60%, #047857 100%)',
                    btnGradient: 'linear-gradient(135deg, #059669 0%, #0d9488 100%)',
                    badgeBgLight: '#ecfdf5',
                    badgeBgDark: 'rgba(16, 185, 129, 0.18)',
                    badgeTextLight: '#065f46',
                    badgeTextDark: '#6ee7b7',
                    badgeBorderLight: '#a7f3d0',
                    badgeBorderDark: 'rgba(16, 185, 129, 0.35)',
                    bgGlowLight: 'rgba(16, 185, 129, 0.18)',
                    bgGlowDark: 'rgba(16, 185, 129, 0.12)'
                },
                ocean: {
                    name: 'Saphir Océan',
                    gradientClass: 'bg-gradient-to-tr from-blue-600 to-cyan-400',
                    primary: '#2563eb',
                    hover: '#1d4ed8',
                    secondary: '#0284c7',
                    accent: '#38bdf8',
                    glow: 'rgba(37, 99, 235, 0.45)',
                    gradient: 'linear-gradient(135deg, #2563eb 0%, #0284c7 60%, #1d4ed8 100%)',
                    btnGradient: 'linear-gradient(135deg, #2563eb 0%, #0284c7 100%)',
                    badgeBgLight: '#eff6ff',
                    badgeBgDark: 'rgba(37, 99, 235, 0.18)',
                    badgeTextLight: '#1e40af',
                    badgeTextDark: '#93c5fd',
                    badgeBorderLight: '#bfdbfe',
                    badgeBorderDark: 'rgba(37, 99, 235, 0.35)',
                    bgGlowLight: 'rgba(37, 99, 235, 0.18)',
                    bgGlowDark: 'rgba(37, 99, 235, 0.12)'
                },
                amethyst: {
                    name: 'Améthyste',
                    gradientClass: 'bg-gradient-to-tr from-purple-600 to-pink-500',
                    primary: '#7c3aed',
                    hover: '#6d28d9',
                    secondary: '#9333ea',
                    accent: '#c084fc',
                    glow: 'rgba(124, 58, 237, 0.45)',
                    gradient: 'linear-gradient(135deg, #7c3aed 0%, #9333ea 60%, #6d28d9 100%)',
                    btnGradient: 'linear-gradient(135deg, #7c3aed 0%, #9333ea 100%)',
                    badgeBgLight: '#faf5ff',
                    badgeBgDark: 'rgba(124, 58, 237, 0.18)',
                    badgeTextLight: '#5b21b6',
                    badgeTextDark: '#d8b4fe',
                    badgeBorderLight: '#e9d5ff',
                    badgeBorderDark: 'rgba(124, 58, 237, 0.35)',
                    bgGlowLight: 'rgba(124, 58, 237, 0.18)',
                    bgGlowDark: 'rgba(124, 58, 237, 0.12)'
                },
                sunset: {
                    name: 'Crépuscule',
                    gradientClass: 'bg-gradient-to-tr from-amber-500 to-rose-500',
                    primary: '#ea580c',
                    hover: '#c2410c',
                    secondary: '#e11d48',
                    accent: '#fb923c',
                    glow: 'rgba(234, 88, 12, 0.45)',
                    gradient: 'linear-gradient(135deg, #ea580c 0%, #e11d48 60%, #c2410c 100%)',
                    btnGradient: 'linear-gradient(135deg, #ea580c 0%, #e11d48 100%)',
                    badgeBgLight: '#fff7ed',
                    badgeBgDark: 'rgba(234, 88, 12, 0.18)',
                    badgeTextLight: '#9a3412',
                    badgeTextDark: '#fed7aa',
                    badgeBorderLight: '#fed7aa',
                    badgeBorderDark: 'rgba(234, 88, 12, 0.35)',
                    bgGlowLight: 'rgba(234, 88, 12, 0.18)',
                    bgGlowDark: 'rgba(234, 88, 12, 0.12)'
                },
                cyber: {
                    name: 'Cyber Néon',
                    gradientClass: 'bg-gradient-to-tr from-cyan-400 to-emerald-400',
                    primary: '#0891b2',
                    hover: '#0e7490',
                    secondary: '#059669',
                    accent: '#06b6d4',
                    glow: 'rgba(6, 182, 212, 0.45)',
                    gradient: 'linear-gradient(135deg, #0891b2 0%, #059669 60%, #0e7490 100%)',
                    btnGradient: 'linear-gradient(135deg, #0891b2 0%, #059669 100%)',
                    badgeBgLight: '#ecfeff',
                    badgeBgDark: 'rgba(6, 182, 212, 0.18)',
                    badgeTextLight: '#155e75',
                    badgeTextDark: '#a5f3fc',
                    badgeBorderLight: '#a5f3fc',
                    badgeBorderDark: 'rgba(6, 182, 212, 0.35)',
                    bgGlowLight: 'rgba(6, 182, 212, 0.18)',
                    bgGlowDark: 'rgba(6, 182, 212, 0.12)'
                }
            };

            function toggleAppThemeDropdown(forceState = null) {
                const menu = document.getElementById('app-theme-dropdown-menu');
                const chevron = document.getElementById('app-theme-chevron');
                if (!menu) return;
                const isClosed = menu.classList.contains('hidden');
                const shouldOpen = forceState !== null ? forceState : isClosed;

                if (shouldOpen) {
                    menu.classList.remove('hidden');
                    chevron?.classList.add('rotate-180');
                } else {
                    menu.classList.add('hidden');
                    chevron?.classList.remove('rotate-180');
                }
            }

            function selectAppTheme(themeName) {
                const theme = appThemeConfig[themeName] || appThemeConfig.emerald;
                const isDark = document.documentElement.classList.contains('dark');
                const root = document.documentElement;

                root.setAttribute('data-theme', themeName);
                root.style.setProperty('--theme-primary', theme.primary);
                root.style.setProperty('--theme-primary-hover', theme.hover);
                root.style.setProperty('--theme-secondary', theme.secondary);
                root.style.setProperty('--theme-accent', theme.accent);
                root.style.setProperty('--theme-glow', theme.glow);
                root.style.setProperty('--theme-gradient', theme.gradient);
                root.style.setProperty('--theme-btn-gradient', theme.btnGradient);
                root.style.setProperty('--theme-badge-bg', isDark ? theme.badgeBgDark : theme.badgeBgLight);
                root.style.setProperty('--theme-badge-text', isDark ? theme.badgeTextDark : theme.badgeTextLight);
                root.style.setProperty('--theme-badge-border', isDark ? theme.badgeBorderDark : theme.badgeBorderLight);
                root.style.setProperty('--theme-bg-glow', isDark ? theme.bgGlowDark : theme.bgGlowLight);

                localStorage.setItem('gut_auth_theme', themeName);

                // Update navbar button UI
                const activeDot = document.getElementById('app-active-theme-dot');
                const activeName = document.getElementById('app-active-theme-name');
                if (activeDot) {
                    activeDot.className = 'h-3.5 w-3.5 rounded-full shadow-sm ring-2 ring-white/40 dark:ring-white/20 ' + theme.gradientClass;
                }
                if (activeName) {
                    activeName.textContent = theme.name;
                }

                // Update checkmarks in dropdown
                document.querySelectorAll('.app-theme-option').forEach(option => {
                    const check = option.querySelector('.app-check-icon');
                    if (option.getAttribute('data-theme') === themeName) {
                        check?.classList.remove('hidden');
                        option.classList.add('bg-slate-100', 'dark:bg-slate-800');
                    } else {
                        check?.classList.add('hidden');
                        option.classList.remove('bg-slate-100', 'dark:bg-slate-800');
                    }
                });

                toggleAppThemeDropdown(false);
            }

            function toggleAppDarkMode() {
                const root = document.documentElement;
                const isDark = root.classList.toggle('dark');
                localStorage.setItem('gut_auth_mode', isDark ? 'dark' : 'light');
                updateAppModeIcons(isDark);
                const currentTheme = localStorage.getItem('gut_auth_theme') || 'emerald';
                selectAppTheme(currentTheme);
            }

            function updateAppModeIcons(isDark) {
                const sun = document.getElementById('app-sun-icon');
                const moon = document.getElementById('app-moon-icon');
                const sunMobile = document.getElementById('app-sun-icon-mobile');
                const moonMobile = document.getElementById('app-moon-icon-mobile');

                if (isDark) {
                    sun?.classList.remove('hidden');
                    moon?.classList.add('hidden');
                    sunMobile?.classList.remove('hidden');
                    moonMobile?.classList.add('hidden');
                } else {
                    sun?.classList.add('hidden');
                    moon?.classList.remove('hidden');
                    sunMobile?.classList.add('hidden');
                    moonMobile?.classList.remove('hidden');
                }
            }

            // Initialize app theme state
            (function() {
                const savedTheme = localStorage.getItem('gut_auth_theme') || 'emerald';
                selectAppTheme(savedTheme);

                const savedMode = localStorage.getItem('gut_auth_mode');
                const isDark = savedMode === 'dark' || (!savedMode && window.matchMedia('(prefers-color-scheme: dark)').matches);
                updateAppModeIcons(isDark);

                // Close dropdown on outside click
                document.addEventListener('click', function(e) {
                    const container = document.getElementById('app-theme-dropdown-container');
                    if (container && !container.contains(e.target)) {
                        toggleAppThemeDropdown(false);
                    }
                });
            })();
        </script>
    </body>
</html>
