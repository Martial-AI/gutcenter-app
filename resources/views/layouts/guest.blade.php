<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }} - {{ __('Connexion') }}</title>
        <link rel="icon" type="image/png" href="{{ asset('images/gut-logo.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800|outfit:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            :root {
                --theme-primary: #059669;
                --theme-primary-hover: #047857;
                --theme-secondary: #0d9488;
                --theme-accent: #10b981;
                --theme-glow: rgba(16, 185, 129, 0.4);
                --theme-gradient: linear-gradient(135deg, #059669 0%, #0d9488 50%, #0284c7 100%);
                --theme-btn-gradient: linear-gradient(135deg, #059669 0%, #0d9488 100%);
                --theme-orb-1: #10b981;
                --theme-orb-2: #06b6d4;
                --theme-orb-3: #3b82f6;
                --theme-bg: #0b1120;
                --theme-card-bg: rgba(15, 23, 42, 0.78);
                --theme-card-border: rgba(255, 255, 255, 0.12);
                --theme-text-primary: #f8fafc;
                --theme-text-secondary: #94a3b8;
                --theme-input-bg: rgba(15, 23, 42, 0.6);
                --theme-input-border: rgba(255, 255, 255, 0.15);
            }

            html:not(.dark) {
                --theme-bg: #f8fafc;
                --theme-card-bg: rgba(255, 255, 255, 0.88);
                --theme-card-border: rgba(255, 255, 255, 0.8);
                --theme-text-primary: #0f172a;
                --theme-text-secondary: #64748b;
                --theme-input-bg: rgba(255, 255, 255, 0.95);
                --theme-input-border: rgba(203, 213, 225, 0.8);
            }

            body {
                font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
                transition: background-color 0.4s ease, color 0.4s ease;
            }

            /* Animated Mesh & Orbs */
            .orb-mesh {
                position: fixed;
                inset: 0;
                pointer-events: none;
                z-index: 0;
                overflow: hidden;
            }

            .orb {
                position: absolute;
                border-radius: 9999px;
                filter: blur(85px);
                opacity: 0.65;
                transition: background-color 0.8s ease, transform 0.8s ease;
                will-change: transform;
            }

            html:not(.dark) .orb {
                opacity: 0.42;
                filter: blur(95px);
            }

            .orb-1 {
                width: 480px;
                height: 480px;
                top: -10%;
                left: -5%;
                background: var(--theme-orb-1);
                animation: floatOrb1 20s infinite alternate ease-in-out;
            }

            .orb-2 {
                width: 540px;
                height: 540px;
                bottom: -15%;
                right: -8%;
                background: var(--theme-orb-2);
                animation: floatOrb2 25s infinite alternate ease-in-out;
            }

            .orb-3 {
                width: 380px;
                height: 380px;
                top: 45%;
                left: 45%;
                background: var(--theme-orb-3);
                animation: floatOrb3 22s infinite alternate ease-in-out;
            }

            @keyframes floatOrb1 {
                0% { transform: translate(0, 0) scale(1); }
                50% { transform: translate(120px, 80px) scale(1.18); }
                100% { transform: translate(-40px, 140px) scale(0.92); }
            }

            @keyframes floatOrb2 {
                0% { transform: translate(0, 0) scale(1); }
                50% { transform: translate(-140px, -90px) scale(1.15); }
                100% { transform: translate(60px, -60px) scale(0.88); }
            }

            @keyframes floatOrb3 {
                0% { transform: translate(0, 0) scale(0.9); }
                50% { transform: translate(-90px, 70px) scale(1.22); }
                100% { transform: translate(110px, -80px) scale(1.05); }
            }

            /* Subtle grid overlay */
            .bg-grid-pattern {
                background-image: radial-gradient(rgba(255, 255, 255, 0.08) 1px, transparent 1px);
                background-size: 28px 28px;
            }
            html:not(.dark) .bg-grid-pattern {
                background-image: radial-gradient(rgba(15, 23, 42, 0.06) 1px, transparent 1px);
            }

            /* Theme dynamic accents */
            .theme-text-glow {
                text-shadow: 0 0 24px var(--theme-glow);
            }
            .theme-btn-gradient {
                background: var(--theme-btn-gradient) !important;
            }
            .theme-btn-gradient:hover {
                filter: brightness(1.08);
                box-shadow: 0 10px 25px -5px var(--theme-glow);
            }
            .theme-border-focus:focus-within {
                border-color: var(--theme-primary) !important;
                box-shadow: 0 0 0 4px var(--theme-glow) !important;
            }

            /* Shimmer effect for buttons */
            .shimmer-btn {
                position: relative;
                overflow: hidden;
            }
            .shimmer-btn::after {
                content: '';
                position: absolute;
                top: -50%;
                left: -60%;
                width: 40%;
                height: 200%;
                background: linear-gradient(
                    60deg,
                    rgba(255, 255, 255, 0) 0%,
                    rgba(255, 255, 255, 0.28) 50%,
                    rgba(255, 255, 255, 0) 100%
                );
                transform: rotate(25deg);
                animation: shimmerSweep 4.5s infinite;
            }
            @keyframes shimmerSweep {
                0% { left: -70%; }
                25%, 100% { left: 160%; }
            }
        </style>
    </head>
    <body class="min-h-screen bg-[var(--theme-bg)] text-[var(--theme-text-primary)] antialiased relative selection:bg-emerald-500 selection:text-white flex flex-col justify-between">
        
        <!-- Animated Background Mesh -->
        <div class="orb-mesh bg-grid-pattern">
            <div class="orb orb-1"></div>
            <div class="orb orb-2"></div>
            <div class="orb orb-3"></div>
        </div>

        <!-- Top Navigation & Controls Bar -->
        <header class="relative z-30 w-full px-4 sm:px-8 py-4 sm:py-6 flex items-center justify-between">
            <!-- Brand Link -->
            <a href="/" class="flex items-center gap-3 group">
                <div class="relative flex h-10 w-10 sm:h-11 sm:w-11 items-center justify-center rounded-2xl bg-white/10 backdrop-blur-md p-1.5 border border-white/20 shadow-lg transition-transform duration-300 group-hover:scale-105">
                    <img src="{{ asset('images/gut-logo.png') }}" alt="GUT Center" class="h-full w-full object-contain" />
                    <span class="absolute -bottom-0.5 -right-0.5 h-3 w-3 rounded-full bg-emerald-500 ring-2 ring-slate-900 animate-pulse"></span>
                </div>
                <div class="flex flex-col">
                    <span class="text-base sm:text-lg font-bold tracking-tight text-white dark:text-white group-hover:text-emerald-400 transition-colors">GUT Center</span>
                    <span class="text-[11px] font-medium tracking-wide text-slate-400 -mt-0.5 hidden sm:inline-block">{{ __('Système de Gestion Scolaire') }}</span>
                </div>
            </a>

            <!-- Control Pills: Theme Dropdown, Dark/Light Mode, Language -->
            <div class="flex items-center gap-2 sm:gap-3">
                
                <!-- Single Theme Button with Dropdown Menu -->
                <div class="relative" id="theme-dropdown-container">
                    <button 
                        type="button" 
                        onclick="toggleThemeDropdown()" 
                        id="theme-dropdown-btn" 
                        class="flex h-9 sm:h-10 items-center gap-2 rounded-full bg-white/10 dark:bg-slate-900/60 backdrop-blur-xl border border-white/20 dark:border-white/10 px-3 sm:px-3.5 text-xs font-semibold text-slate-200 hover:text-white hover:bg-white/20 transition-all shadow-lg focus:outline-none cursor-pointer"
                        title="Changer la couleur du thème"
                    >
                        <span id="active-theme-dot" class="h-3.5 w-3.5 rounded-full shadow-sm ring-2 ring-white/30 bg-gradient-to-tr from-emerald-600 to-teal-400"></span>
                        <span id="active-theme-name" class="font-medium text-slate-200">Émeraude</span>
                        <svg id="theme-chevron" class="h-3.5 w-3.5 opacity-70 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <!-- Themes Dropdown Menu -->
                    <div 
                        id="theme-dropdown-menu" 
                        class="absolute right-0 mt-2 hidden w-56 sm:w-60 overflow-hidden rounded-2xl border border-white/20 dark:border-white/10 bg-white/95 dark:bg-slate-900/95 backdrop-blur-2xl p-1.5 shadow-2xl z-50 animate-fade-in"
                    >
                        <div class="px-3 py-2 text-[11px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-200/50 dark:border-slate-800/80 mb-1 flex items-center justify-between">
                            <span>Couleur du Thème</span>
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4 3.5 3.5 0 017 0 4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                        </div>
                        
                        <!-- Emerald -->
                        <button type="button" onclick="selectTheme('emerald')" class="theme-option flex w-full items-center justify-between gap-2.5 rounded-xl px-2.5 py-2 text-left text-xs transition-all hover:bg-slate-100 dark:hover:bg-slate-800/80 group" data-theme="emerald">
                            <div class="flex items-center gap-2.5">
                                <span class="h-4 w-4 rounded-full bg-gradient-to-tr from-emerald-600 to-teal-400 shadow-sm ring-1 ring-white/20"></span>
                                <div class="flex flex-col">
                                    <span class="font-semibold text-slate-800 dark:text-slate-100 group-hover:text-emerald-500">Émeraude</span>
                                    <span class="text-[10px] text-slate-400">Signature GUT Center</span>
                                </div>
                            </div>
                            <svg class="check-icon w-4 h-4 text-emerald-500 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </button>

                        <!-- Ocean -->
                        <button type="button" onclick="selectTheme('ocean')" class="theme-option flex w-full items-center justify-between gap-2.5 rounded-xl px-2.5 py-2 text-left text-xs transition-all hover:bg-slate-100 dark:hover:bg-slate-800/80 group" data-theme="ocean">
                            <div class="flex items-center gap-2.5">
                                <span class="h-4 w-4 rounded-full bg-gradient-to-tr from-blue-600 to-cyan-400 shadow-sm ring-1 ring-white/20"></span>
                                <div class="flex flex-col">
                                    <span class="font-semibold text-slate-800 dark:text-slate-100 group-hover:text-blue-500">Saphir Océan</span>
                                    <span class="text-[10px] text-slate-400">Bleu & Cyan High-Tech</span>
                                </div>
                            </div>
                            <svg class="check-icon w-4 h-4 text-blue-500 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </button>

                        <!-- Amethyst -->
                        <button type="button" onclick="selectTheme('amethyst')" class="theme-option flex w-full items-center justify-between gap-2.5 rounded-xl px-2.5 py-2 text-left text-xs transition-all hover:bg-slate-100 dark:hover:bg-slate-800/80 group" data-theme="amethyst">
                            <div class="flex items-center gap-2.5">
                                <span class="h-4 w-4 rounded-full bg-gradient-to-tr from-purple-600 to-pink-500 shadow-sm ring-1 ring-white/20"></span>
                                <div class="flex flex-col">
                                    <span class="font-semibold text-slate-800 dark:text-slate-100 group-hover:text-purple-500">Améthyste</span>
                                    <span class="text-[10px] text-slate-400">Violet & Magenta</span>
                                </div>
                            </div>
                            <svg class="check-icon w-4 h-4 text-purple-500 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </button>

                        <!-- Sunset -->
                        <button type="button" onclick="selectTheme('sunset')" class="theme-option flex w-full items-center justify-between gap-2.5 rounded-xl px-2.5 py-2 text-left text-xs transition-all hover:bg-slate-100 dark:hover:bg-slate-800/80 group" data-theme="sunset">
                            <div class="flex items-center gap-2.5">
                                <span class="h-4 w-4 rounded-full bg-gradient-to-tr from-amber-500 to-rose-500 shadow-sm ring-1 ring-white/20"></span>
                                <div class="flex flex-col">
                                    <span class="font-semibold text-slate-800 dark:text-slate-100 group-hover:text-amber-500">Crépuscule</span>
                                    <span class="text-[10px] text-slate-400">Ambre & Corail</span>
                                </div>
                            </div>
                            <svg class="check-icon w-4 h-4 text-amber-500 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </button>

                        <!-- Cyber -->
                        <button type="button" onclick="selectTheme('cyber')" class="theme-option flex w-full items-center justify-between gap-2.5 rounded-xl px-2.5 py-2 text-left text-xs transition-all hover:bg-slate-100 dark:hover:bg-slate-800/80 group" data-theme="cyber">
                            <div class="flex items-center gap-2.5">
                                <span class="h-4 w-4 rounded-full bg-gradient-to-tr from-cyan-400 to-emerald-400 shadow-sm ring-1 ring-white/20"></span>
                                <div class="flex flex-col">
                                    <span class="font-semibold text-slate-800 dark:text-slate-100 group-hover:text-cyan-500">Cyber Néon</span>
                                    <span class="text-[10px] text-slate-400">Holographique</span>
                                </div>
                            </div>
                            <svg class="check-icon w-4 h-4 text-cyan-500 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </button>
                    </div>
                </div>

                <!-- Dark / Light Mode Toggle -->
                <button type="button" onclick="toggleDarkMode()" id="mode-toggle-btn" class="flex h-9 w-9 sm:h-10 sm:w-10 items-center justify-center rounded-full bg-white/10 dark:bg-slate-900/60 backdrop-blur-xl border border-white/20 dark:border-white/10 text-slate-200 hover:text-white hover:bg-white/20 transition-all shadow-lg focus:outline-none cursor-pointer" title="Basculer Mode Clair / Sombre">
                    <!-- Sun Icon (shows in dark) -->
                    <svg id="sun-icon" class="h-4 w-4 sm:h-5 sm:w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <!-- Moon Icon (shows in light) -->
                    <svg id="moon-icon" class="h-4 w-4 sm:h-5 sm:w-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                </button>

                <!-- Language Switcher Pill -->
                <div class="relative">
                    <button type="button" onclick="toggleLanguageDropdown()" id="lang-dropdown-btn" class="flex h-9 sm:h-10 items-center gap-1.5 rounded-full bg-white/10 dark:bg-slate-900/60 backdrop-blur-xl border border-white/20 dark:border-white/10 px-3 text-xs font-bold uppercase tracking-wider text-slate-200 hover:text-white hover:bg-white/20 transition-all shadow-lg cursor-pointer">
                        <span>{{ strtoupper(app()->getLocale()) }}</span>
                        <svg id="lang-chevron" class="h-3.5 w-3.5 opacity-70 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div id="guest-lang-dropdown" class="absolute right-0 mt-2 hidden w-28 overflow-hidden rounded-2xl border border-white/20 dark:border-white/10 bg-white/95 dark:bg-slate-900/95 backdrop-blur-2xl py-1.5 shadow-2xl z-50">
                        @foreach (['fr' => 'Français', 'en' => 'English'] as $locale => $label)
                            <form method="POST" action="{{ route('language.switch', $locale) }}">
                                @csrf
                                <button type="submit" class="flex w-full items-center justify-between px-3.5 py-2 text-left text-xs font-medium transition-colors hover:bg-emerald-500/10 hover:text-emerald-500 {{ app()->getLocale() === $locale ? 'text-emerald-500 font-bold' : 'text-slate-700 dark:text-slate-300' }}">
                                    <span>{{ $label }}</span>
                                    @if(app()->getLocale() === $locale)
                                        <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    @endif
                                </button>
                            </form>
                        @endforeach
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Center Area -->
        <main class="relative z-10 flex flex-1 items-center justify-center px-4 py-6 sm:py-10">
            <div class="w-full max-w-lg">
                {{ $slot }}
            </div>
        </main>

        <!-- Modern Footer Security Note -->
        <footer class="relative z-10 w-full px-4 py-4 text-center text-xs text-slate-400/80">
            <div class="flex flex-wrap items-center justify-center gap-3 text-[11px]">
                <span class="inline-flex items-center gap-1.5">
                    <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    {{ __('Système Opérationnel') }}
                </span>
                <span>•</span>
                <span>{{ __('Chiffrement de Niveau Académique') }}</span>
                <span>•</span>
                <span>&copy; {{ date('Y') }} GUT Center</span>
            </div>
        </footer>

        <!-- Modals -->
        @if (session('status'))
            <div id="guest-status-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 backdrop-blur-md p-4 animate-fade-in">
                <div class="w-full max-w-sm rounded-2xl bg-white dark:bg-slate-900 p-6 text-center shadow-2xl border border-emerald-500/20">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-emerald-500/10 text-emerald-500 mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white">{{ __('Action successful') }}</h2>
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">{{ session('status') }}</p>
                    <button type="button" onclick="document.getElementById('guest-status-modal').remove()" class="mt-5 w-full rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 py-2.5 text-sm font-semibold text-white shadow-md hover:shadow-lg transition-all">{{ __('OK') }}</button>
                </div>
            </div>
        @endif

        @if ((session('error') || $errors->any()) && $errors->first('email') !== __('Your account has been suspended. Contact the Manager.'))
            <div id="guest-error-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 backdrop-blur-md p-4 animate-fade-in">
                <div class="w-full max-w-sm rounded-2xl bg-white dark:bg-slate-900 p-6 text-center shadow-2xl border border-rose-500/20">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-rose-500/10 text-rose-500 mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white">{{ __('Action not completed') }}</h2>
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">{{ session('error') ?: $errors->first() }}</p>
                    <button type="button" onclick="document.getElementById('guest-error-modal').remove()" class="mt-5 w-full rounded-xl bg-slate-800 dark:bg-slate-700 py-2.5 text-sm font-semibold text-white shadow-md hover:bg-slate-700 transition-all">{{ __('OK') }}</button>
                </div>
            </div>
        @endif

        <!-- Client Theme Management Logic -->
        <script>
            const themeConfig = {
                emerald: {
                    name: 'Émeraude',
                    gradientClass: 'bg-gradient-to-tr from-emerald-600 to-teal-400',
                    primary: '#059669',
                    hover: '#047857',
                    secondary: '#0d9488',
                    accent: '#10b981',
                    glow: 'rgba(16, 185, 129, 0.45)',
                    btnGradient: 'linear-gradient(135deg, #059669 0%, #0d9488 100%)',
                    orb1: '#10b981',
                    orb2: '#06b6d4',
                    orb3: '#3b82f6'
                },
                ocean: {
                    name: 'Saphir Océan',
                    gradientClass: 'bg-gradient-to-tr from-blue-600 to-cyan-400',
                    primary: '#2563eb',
                    hover: '#1d4ed8',
                    secondary: '#0284c7',
                    accent: '#38bdf8',
                    glow: 'rgba(37, 99, 235, 0.45)',
                    btnGradient: 'linear-gradient(135deg, #2563eb 0%, #0284c7 100%)',
                    orb1: '#3b82f6',
                    orb2: '#0ea5e9',
                    orb3: '#6366f1'
                },
                amethyst: {
                    name: 'Améthyste',
                    gradientClass: 'bg-gradient-to-tr from-purple-600 to-pink-500',
                    primary: '#7c3aed',
                    hover: '#6d28d9',
                    secondary: '#9333ea',
                    accent: '#c084fc',
                    glow: 'rgba(124, 58, 237, 0.45)',
                    btnGradient: 'linear-gradient(135deg, #7c3aed 0%, #9333ea 100%)',
                    orb1: '#8b5cf6',
                    orb2: '#d946ef',
                    orb3: '#ec4899'
                },
                sunset: {
                    name: 'Crépuscule',
                    gradientClass: 'bg-gradient-to-tr from-amber-500 to-rose-500',
                    primary: '#ea580c',
                    hover: '#c2410c',
                    secondary: '#f59e0b',
                    accent: '#fb923c',
                    glow: 'rgba(234, 88, 12, 0.45)',
                    btnGradient: 'linear-gradient(135deg, #ea580c 0%, #f59e0b 100%)',
                    orb1: '#f97316',
                    orb2: '#f59e0b',
                    orb3: '#f43f5e'
                },
                cyber: {
                    name: 'Cyber Néon',
                    gradientClass: 'bg-gradient-to-tr from-cyan-400 to-emerald-400',
                    primary: '#06b6d4',
                    hover: '#0891b2',
                    secondary: '#10b981',
                    accent: '#22d3ee',
                    glow: 'rgba(6, 182, 212, 0.45)',
                    btnGradient: 'linear-gradient(135deg, #06b6d4 0%, #10b981 100%)',
                    orb1: '#06b6d4',
                    orb2: '#10b981',
                    orb3: '#8b5cf6'
                }
            };

            function toggleThemeDropdown(forceState = null) {
                const menu = document.getElementById('theme-dropdown-menu');
                const chevron = document.getElementById('theme-chevron');
                const isClosed = menu.classList.contains('hidden');
                const shouldOpen = forceState !== null ? forceState : isClosed;

                if (shouldOpen) {
                    menu.classList.remove('hidden');
                    chevron.classList.add('rotate-180');
                    // Close language dropdown if open
                    document.getElementById('guest-lang-dropdown')?.classList.add('hidden');
                    document.getElementById('lang-chevron')?.classList.remove('rotate-180');
                } else {
                    menu.classList.add('hidden');
                    chevron.classList.remove('rotate-180');
                }
            }

            function toggleLanguageDropdown() {
                const menu = document.getElementById('guest-lang-dropdown');
                const chevron = document.getElementById('lang-chevron');
                const isClosed = menu.classList.contains('hidden');

                if (isClosed) {
                    menu.classList.remove('hidden');
                    chevron.classList.add('rotate-180');
                    // Close theme dropdown if open
                    toggleThemeDropdown(false);
                } else {
                    menu.classList.add('hidden');
                    chevron.classList.remove('rotate-180');
                }
            }

            function selectTheme(themeName) {
                const theme = themeConfig[themeName] || themeConfig.emerald;
                const root = document.documentElement;
                root.style.setProperty('--theme-primary', theme.primary);
                root.style.setProperty('--theme-primary-hover', theme.hover);
                root.style.setProperty('--theme-secondary', theme.secondary);
                root.style.setProperty('--theme-accent', theme.accent);
                root.style.setProperty('--theme-glow', theme.glow);
                root.style.setProperty('--theme-btn-gradient', theme.btnGradient);
                root.style.setProperty('--theme-orb-1', theme.orb1);
                root.style.setProperty('--theme-orb-2', theme.orb2);
                root.style.setProperty('--theme-orb-3', theme.orb3);

                localStorage.setItem('gut_auth_theme', themeName);

                // Update trigger button UI
                const activeDot = document.getElementById('active-theme-dot');
                const activeName = document.getElementById('active-theme-name');
                if (activeDot) {
                    activeDot.className = 'h-3.5 w-3.5 rounded-full shadow-sm ring-2 ring-white/30 ' + theme.gradientClass;
                }
                if (activeName) {
                    activeName.textContent = theme.name;
                }

                // Update checkmark in dropdown
                document.querySelectorAll('.theme-option').forEach(option => {
                    const check = option.querySelector('.check-icon');
                    if (option.getAttribute('data-theme') === themeName) {
                        check?.classList.remove('hidden');
                        option.classList.add('bg-slate-100/70', 'dark:bg-slate-800/60');
                    } else {
                        check?.classList.add('hidden');
                        option.classList.remove('bg-slate-100/70', 'dark:bg-slate-800/60');
                    }
                });

                // Close dropdown
                toggleThemeDropdown(false);
            }

            function toggleDarkMode() {
                const root = document.documentElement;
                const isDark = root.classList.toggle('dark');
                localStorage.setItem('gut_auth_mode', isDark ? 'dark' : 'light');
                updateModeIcons(isDark);
            }

            function updateModeIcons(isDark) {
                const sun = document.getElementById('sun-icon');
                const moon = document.getElementById('moon-icon');
                if (isDark) {
                    sun.classList.remove('hidden');
                    moon.classList.add('hidden');
                } else {
                    sun.classList.add('hidden');
                    moon.classList.remove('hidden');
                }
            }

            // Initialize saved preferences
            (function () {
                const savedTheme = localStorage.getItem('gut_auth_theme') || 'emerald';
                selectTheme(savedTheme);

                const savedMode = localStorage.getItem('gut_auth_mode');
                if (savedMode === 'light') {
                    document.documentElement.classList.remove('dark');
                    updateModeIcons(false);
                } else {
                    document.documentElement.classList.add('dark');
                    updateModeIcons(true);
                }

                // Close dropdowns on outside click
                document.addEventListener('click', function(e) {
                    const themeContainer = document.getElementById('theme-dropdown-container');
                    const langContainer = document.getElementById('lang-dropdown-btn')?.parentElement;
                    
                    if (themeContainer && !themeContainer.contains(e.target)) {
                        toggleThemeDropdown(false);
                    }
                    if (langContainer && !langContainer.contains(e.target)) {
                        document.getElementById('guest-lang-dropdown')?.classList.add('hidden');
                        document.getElementById('lang-chevron')?.classList.remove('rotate-180');
                    }
                });
            })();
        </script>
    </body>
</html>
