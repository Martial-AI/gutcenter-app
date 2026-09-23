@php
    $canViewActivityNotifications = auth()->user()->can('activity.view');
    $canDeleteActivityNotifications = auth()->user()->can('activity.delete');
    $activityNotifications = collect(); $unreadActivityIds = collect(); $unreadActivityCount = 0; $latestActivityAt = now()->toISOString();
    if ($canViewActivityNotifications) {
        $activityQuery = \Spatie\Activitylog\Models\Activity::query()->with(['causer', 'subject'])->latest();
        $activityNotifications = (clone $activityQuery)->get();
        $latestActivityAt = optional($activityNotifications->first()?->created_at)->toISOString() ?? now()->toISOString();
        $lastSeen = auth()->user()->activity_notifications_read_at;
        $readIds = auth()->user()->activity_notifications_read_ids ?? [];
        $unreadActivityIds = (clone $activityQuery)->when($lastSeen, fn ($query) => $query->where('created_at', '>', $lastSeen))->whereNotIn('id', $readIds)->pluck('id');
        $unreadActivityCount = $unreadActivityIds->count();
    }
@endphp
<nav x-data="{ open: false }" class="sticky top-0 z-40 border-b border-emerald-100/80 bg-white/90 shadow-sm backdrop-blur">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8"><div class="flex h-16 justify-between"><div class="flex"><div class="flex shrink-0 items-center"><a href="{{ route('dashboard') }}" class="flex items-center gap-2"><img src="{{ asset('images/gut-logo.png') }}" alt="GUT Center" class="h-8 w-8 object-contain"><span class="text-lg font-bold tracking-tight text-emerald-800">GUT Center</span></a></div><div class="hidden space-x-2 items-center sm:ms-8 sm:flex"><x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">{{ __('Dashboard') }}</x-nav-link>@can('students.view')<x-nav-link :href="route('students.index')" :active="request()->routeIs('students.*')">{{ __('Students') }}</x-nav-link>@endcan @can('classes.manage')<x-nav-link :href="route('classes.index')" :active="request()->routeIs('classes.*')">{{ __('Classes') }}</x-nav-link>@endcan @can('programs.view')<x-nav-link :href="route('programs.index')" :active="request()->routeIs('programs.*')">{{ __('Programs') }}</x-nav-link>@endcan @can('attendance.view')<x-nav-link :href="route('attendance.index')" :active="request()->routeIs('attendance.*')">{{ __('Attendance') }}</x-nav-link>@endcan @can('statistics.view')<x-nav-link :href="route('statistics.index')" :active="request()->routeIs('statistics.*')">{{ __('Statistics') }}</x-nav-link>@endcan</div></div>
        <div class="hidden items-center gap-2 sm:flex">
            @if($canViewActivityNotifications)
                <button id="activity-notification-button" type="button" onclick="openActivityModal()" title="{{ __('Activity history') }}" class="relative inline-flex h-10 w-10 items-center justify-center rounded-full text-slate-600 dark:text-slate-300 hover:bg-emerald-50 dark:hover:bg-slate-800 hover:text-emerald-700 dark:hover:text-emerald-400 transition-colors">
                    <svg class="relative z-0 h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0 1 18 14.158V11a6 6 0 0 0-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 0 1-6 0v-1m6 0H9"/>
                    </svg>
                    <span id="activity-badge" class="{{ $unreadActivityCount ? '' : 'hidden ' }}absolute -right-1 -top-1 z-10 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-600 px-1 text-[11px] font-bold text-white ring-2 ring-white dark:ring-slate-900">{{ $unreadActivityCount > 99 ? '99+' : $unreadActivityCount }}</span>
                </button>
            @endif

            <!-- App Theme Selector Dropdown -->
            <div class="relative" id="app-theme-dropdown-container">
                <button 
                    type="button" 
                    onclick="toggleAppThemeDropdown()" 
                    id="app-theme-dropdown-btn" 
                    class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200/90 dark:border-slate-700 bg-white/80 dark:bg-slate-800/80 shadow-sm hover:bg-slate-100 dark:hover:bg-slate-700 transition-all focus:outline-none cursor-pointer"
                    title="Couleur du thème"
                >
                    <span id="app-active-theme-dot" class="h-4 w-4 rounded-full shadow-sm ring-2 ring-white/50 dark:ring-white/30 bg-gradient-to-tr from-emerald-600 to-teal-400"></span>
                </button>

                <!-- Themes Dropdown Menu (only circular color swatches) -->
                <div 
                    id="app-theme-dropdown-menu" 
                    class="absolute right-0 mt-2 hidden overflow-hidden rounded-2xl border border-slate-200/90 dark:border-slate-700 bg-white/95 dark:bg-slate-900/95 backdrop-blur-2xl p-2 shadow-2xl z-50 animate-fade-in"
                >
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="selectAppTheme('emerald')" class="app-theme-option relative flex h-7 w-7 items-center justify-center rounded-full bg-gradient-to-tr from-emerald-600 to-teal-400 shadow-sm ring-2 ring-transparent transition-all hover:scale-110 focus:outline-none cursor-pointer" data-theme="emerald">
                            <svg class="app-check-icon w-3.5 h-3.5 text-white hidden drop-shadow" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        </button>
                        <button type="button" onclick="selectAppTheme('ocean')" class="app-theme-option relative flex h-7 w-7 items-center justify-center rounded-full bg-gradient-to-tr from-blue-600 to-cyan-400 shadow-sm ring-2 ring-transparent transition-all hover:scale-110 focus:outline-none cursor-pointer" data-theme="ocean">
                            <svg class="app-check-icon w-3.5 h-3.5 text-white hidden drop-shadow" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        </button>
                        <button type="button" onclick="selectAppTheme('amethyst')" class="app-theme-option relative flex h-7 w-7 items-center justify-center rounded-full bg-gradient-to-tr from-purple-600 to-pink-500 shadow-sm ring-2 ring-transparent transition-all hover:scale-110 focus:outline-none cursor-pointer" data-theme="amethyst">
                            <svg class="app-check-icon w-3.5 h-3.5 text-white hidden drop-shadow" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        </button>
                        <button type="button" onclick="selectAppTheme('sunset')" class="app-theme-option relative flex h-7 w-7 items-center justify-center rounded-full bg-gradient-to-tr from-amber-500 to-rose-500 shadow-sm ring-2 ring-transparent transition-all hover:scale-110 focus:outline-none cursor-pointer" data-theme="sunset">
                            <svg class="app-check-icon w-3.5 h-3.5 text-white hidden drop-shadow" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        </button>
                        <button type="button" onclick="selectAppTheme('cyber')" class="app-theme-option relative flex h-7 w-7 items-center justify-center rounded-full bg-gradient-to-tr from-cyan-400 to-emerald-400 shadow-sm ring-2 ring-transparent transition-all hover:scale-110 focus:outline-none cursor-pointer" data-theme="cyber">
                            <svg class="app-check-icon w-3.5 h-3.5 text-white hidden drop-shadow" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Dark / Light Mode Toggle Button -->
            <button 
                type="button" 
                onclick="toggleAppDarkMode()" 
                id="app-mode-toggle-btn" 
                class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200/90 dark:border-slate-700 bg-white/80 dark:bg-slate-800/80 text-slate-600 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 shadow-sm transition-all focus:outline-none cursor-pointer" 
                title="Basculer Mode Clair / Sombre"
            >
                <svg id="app-sun-icon" class="h-4 w-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                <svg id="app-moon-icon" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
            </button>

            <!-- Language Switcher Dropdown -->
            <x-dropdown align="right" width="24">
                <x-slot name="trigger">
                    <button class="inline-flex items-center rounded-xl border border-slate-200/90 dark:border-slate-700 bg-white/80 dark:bg-slate-800/80 px-2.5 py-1.5 text-xs font-semibold text-slate-700 dark:text-slate-200 shadow-sm hover:bg-slate-100 dark:hover:bg-slate-700 transition-all cursor-pointer">
                        {{ strtoupper(app()->getLocale()) }}
                        <svg class="ms-1 h-3.5 w-3.5 fill-current opacity-70" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4-4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg>
                    </button>
                </x-slot>
                <x-slot name="content">
                    <form method="POST" action="{{ route('language.switch', 'fr') }}">@csrf<button class="w-full px-4 py-2 text-left text-xs font-medium hover:bg-emerald-50 dark:hover:bg-slate-800">Français (FR)</button></form>
                    <form method="POST" action="{{ route('language.switch', 'en') }}">@csrf<button class="w-full px-4 py-2 text-left text-xs font-medium hover:bg-emerald-50 dark:hover:bg-slate-800">English (EN)</button></form>
                </x-slot>
            </x-dropdown>

            <!-- User Profile Dropdown -->
            <span class="text-xs font-semibold text-slate-700 dark:text-slate-200 ml-1">{{ Auth::user()->name }}</span>
            <x-dropdown align="right" width="48">
                <x-slot name="trigger">
                    <button title="{{ __('Profile') }}" aria-label="{{ __('Profile') }}" class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 shadow-sm ring-1 ring-emerald-300 dark:ring-emerald-700 hover:scale-105 transition-transform cursor-pointer">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12a4.5 4.5 0 1 0 0-9 4.5 4.5 0 0 0 0 9Zm-8.25 9a8.25 8.25 0 0 1 16.5 0 .75.75 0 0 1-.75-.75H4.5a.75.75 0 0 1-.75-.75Z"/></svg>
                    </button>
                </x-slot>
                <x-slot name="content">
                    @can('roles.manage')
                        <x-dropdown-link :href="route('admin.users.index')">{{ __('Accounts') }}</x-dropdown-link>
                        <x-dropdown-link :href="route('work-schedules.index')">{{ __('Work Schedules') }}</x-dropdown-link>
                    @endcan 
                    @can('expenses.view')<x-dropdown-link :href="route('expenses.index')">{{ __('Expenses') }}</x-dropdown-link>@endcan
                    <x-dropdown-link :href="route('profile.edit')">{{ __('Profile') }}</x-dropdown-link>
                    <form method="POST" action="{{ route('logout') }}">@csrf<x-dropdown-link :href="route('logout')" onclick="event.preventDefault();this.closest('form').submit();">{{ __('Log Out') }}</x-dropdown-link></form>
                </x-slot>
            </x-dropdown>
        </div>
        
        <!-- Mobile Bar Right Icons -->
        <div class="-me-2 flex items-center gap-1 sm:hidden">
            <!-- Mobile Day/Night toggle -->
            <button type="button" onclick="toggleAppDarkMode()" class="inline-flex h-9 w-9 items-center justify-center rounded-full text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">
                <svg id="app-sun-icon-mobile" class="h-5 w-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                <svg id="app-moon-icon-mobile" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
            </button>
            @if($canViewActivityNotifications)<button type="button" onclick="openActivityModal()" title="{{ __('Activity history') }}" class="inline-flex h-10 w-10 items-center justify-center rounded-full text-slate-600 dark:text-slate-300 hover:bg-emerald-50 dark:hover:bg-slate-800"><svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0 1 18 14.158V11a6 6 0 0 0-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 0 1-6 0v-1m6 0H9"/></svg></button>@endif
            <a href="{{ route('profile.edit') }}" title="{{ __('Profile') }}" class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300 ring-1 ring-emerald-200 dark:ring-emerald-700"><svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12a4.5 4.5 0 1 0 0-9 4.5 4.5 0 0 0 0 9Zm-8.25 9a8.25 8.25 0 0 1 16.5 0 .75.75 0 0 1-.75-.75H4.5a.75.75 0 0 1-.75-.75Z"/></svg></a>
            <button @click="open = ! open" class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 hover:text-gray-500"><svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24"><path :class="{'hidden': open, 'inline-flex': ! open }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/><path :class="{'hidden': ! open, 'inline-flex': open}" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
        </div>
    </div></div>
    
    <!-- Mobile Drawer -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden border-b border-slate-200 dark:border-slate-800 bg-white/95 dark:bg-slate-900/95 backdrop-blur-xl">
        <div class="space-y-1 pb-3 pt-2">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">{{ __('Dashboard') }}</x-responsive-nav-link>
            @can('students.view')<x-responsive-nav-link :href="route('students.index')" :active="request()->routeIs('students.*')">{{ __('Students') }}</x-responsive-nav-link>@endcan 
            @can('classes.manage')<x-responsive-nav-link :href="route('classes.index')" :active="request()->routeIs('classes.*')">{{ __('Classes') }}</x-responsive-nav-link>@endcan 
            @can('programs.view')<x-responsive-nav-link :href="route('programs.index')" :active="request()->routeIs('programs.*')">{{ __('Programs') }}</x-responsive-nav-link>@endcan 
            @can('attendance.view')<x-responsive-nav-link :href="route('attendance.index')" :active="request()->routeIs('attendance.*')">{{ __('Attendance') }}</x-responsive-nav-link>@endcan @can('statistics.view')<x-responsive-nav-link :href="route('statistics.index')" :active="request()->routeIs('statistics.*')">{{ __('Statistics') }}</x-responsive-nav-link>@endcan 
            @can('roles.manage')
                <x-responsive-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">{{ __('Accounts') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('work-schedules.index')" :active="request()->routeIs('work-schedules.*')">{{ __('Work Schedules') }}</x-responsive-nav-link>
            @endcan
            <x-responsive-nav-link :href="route('profile.edit')" :active="request()->routeIs('profile.*')">{{ __('Profile') }}</x-responsive-nav-link>
        </div>
        
        <!-- Mobile Theme Selection -->
        <div class="border-t border-gray-200 dark:border-slate-800 px-4 py-3">
            <div class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Thème de l'application</div>
            <div class="flex items-center gap-2">
                <button type="button" onclick="selectAppTheme('emerald')" class="h-7 w-7 rounded-full bg-gradient-to-tr from-emerald-600 to-teal-400 ring-2 ring-white/30 shadow-sm transition-transform active:scale-95"></button>
                <button type="button" onclick="selectAppTheme('ocean')" class="h-7 w-7 rounded-full bg-gradient-to-tr from-blue-600 to-cyan-400 ring-2 ring-white/30 shadow-sm transition-transform active:scale-95"></button>
                <button type="button" onclick="selectAppTheme('amethyst')" class="h-7 w-7 rounded-full bg-gradient-to-tr from-purple-600 to-pink-500 ring-2 ring-white/30 shadow-sm transition-transform active:scale-95"></button>
                <button type="button" onclick="selectAppTheme('sunset')" class="h-7 w-7 rounded-full bg-gradient-to-tr from-amber-500 to-rose-500 ring-2 ring-white/30 shadow-sm transition-transform active:scale-95"></button>
                <button type="button" onclick="selectAppTheme('cyber')" class="h-7 w-7 rounded-full bg-gradient-to-tr from-cyan-400 to-emerald-400 ring-2 ring-white/30 shadow-sm transition-transform active:scale-95"></button>
            </div>
        </div>

        <div class="border-t border-gray-200 dark:border-slate-800 px-4 py-3">
            <div class="font-medium text-slate-800 dark:text-slate-100">{{ Auth::user()->name }}</div>
            <div class="text-sm text-gray-500 dark:text-slate-400">{{ Auth::user()->email }}</div>
            <div class="mt-3 flex gap-2">
                <form method="POST" action="{{ route('language.switch', 'fr') }}">@csrf<button class="rounded-lg border border-slate-300 dark:border-slate-700 px-3 py-1 text-xs font-semibold">FR</button></form>
                <form method="POST" action="{{ route('language.switch', 'en') }}">@csrf<button class="rounded-lg border border-slate-300 dark:border-slate-700 px-3 py-1 text-xs font-semibold">EN</button></form>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="rounded-lg border border-red-200 dark:border-red-900/60 px-3 py-1 text-xs font-semibold text-red-600 dark:text-red-400">{{ __('Log Out') }}</button></form>
            </div>
        </div>
    </div>
</nav>
@can('payments.view')@if(!request()->routeIs('students.show'))<script>
document.addEventListener('DOMContentLoaded', () => {
    const studentsUrl = '{{ route('students.index') }}';
    const feesUrl = '{{ route('school-fees.index') }}';
    document.querySelectorAll(`nav a[href="${studentsUrl}"]`).forEach((studentLink) => {
        if (studentLink.parentElement?.querySelector(`a[href="${feesUrl}"]`)) return;
        const link = studentLink.cloneNode(false);
        link.href = feesUrl;
        link.textContent = '{{ __('schoolfees.title') }}';

        const activeClasses = 'inline-flex items-center px-3.5 py-1.5 rounded-xl bg-emerald-50 text-sm font-semibold text-emerald-800 border border-emerald-200/80 shadow-sm transition-all duration-200 active:scale-95';
        const inactiveClasses = 'inline-flex items-center px-3.5 py-1.5 rounded-xl text-sm font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100/70 transition-all duration-200 active:scale-95';

        link.className = window.location.pathname === new URL(feesUrl, window.location.origin).pathname
            ? activeClasses
            : inactiveClasses;

        studentLink.insertAdjacentElement('afterend', link);
    });
});
</script>@endif @endcan
@if($canViewActivityNotifications)<div id="activity-history-modal" class="fixed inset-0 z-[90] hidden items-center justify-center bg-black/40 p-4 backdrop-blur-sm"><div class="flex max-h-[85vh] w-full max-w-2xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl"><div class="flex items-center justify-between border-b border-slate-100 px-6 py-4"><div><h2 class="font-semibold text-slate-900">{{ __('Activity history') }}</h2><p class="text-xs text-slate-500">{{ __('All recorded activities') }}</p></div><button type="button" onclick="closeActivityModal()" class="rounded-lg px-3 py-2 text-sm text-slate-500 hover:bg-slate-100 transition active:scale-95">{{ __('Close') }}</button></div><div id="activity-history-list" class="overflow-y-auto px-6 py-3">@forelse($activityNotifications as $activity)@php($isNew = $unreadActivityIds->contains($activity->id))<div data-activity-id="{{ $activity->id }}" class="activity-entry border-b border-slate-100 py-4 select-none cursor-pointer hover:bg-slate-50/80 transition {{ $isNew ? 'is-new -mx-2 rounded-lg bg-slate-100/70 px-2' : '' }}"><div class="flex gap-3"><span class="activity-new-dot mt-1 h-2.5 w-2.5 shrink-0 rounded-full bg-red-500 {{ $isNew ? '' : 'hidden' }}"></span><div class="min-w-0"><p class="text-sm font-medium text-slate-800">{{ \App\Services\ActivityDescriptionLocalizer::localize($activity->description) }}</p><p class="mt-1 text-xs text-slate-500">{{ $activity->causer?->localizedFunctionLabel() ?: __('System') }} Â· {{ $activity->created_at->format('d/m/Y H:i') }}</p></div></div></div>@empty<div id="no-activity-message" class="py-12 text-center text-sm text-slate-500">{{ __('No history recorded.') }}</div>@endforelse</div><div class="flex items-center justify-between border-t border-slate-100 px-6 py-4"><div id="activity-selection-actions" class="hidden flex items-center gap-3"><label class="flex items-center gap-1.5 text-xs font-medium text-slate-600 cursor-pointer select-none"><input id="activity-select-all" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-emerald-600"> {{ __('Select all') }}</label><button id="activity-delete-btn" type="button" class="rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-rose-700 transition active:scale-95">{{ __('Delete selected') }}</button><button id="activity-cancel-select-btn" type="button" class="text-xs text-slate-500 hover:text-slate-800">{{ __('Cancel') }}</button></div>@if($canDeleteActivityNotifications)<button id="activity-select-all-btn" type="button" class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-100 transition active:scale-95">{{ __('Select items') }}</button>@endif<button type="button" onclick="closeActivityModal()" class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-900 transition active:scale-95">{{ __('Close') }}</button></div></div></div>
<script>
let latestActivityAt='{{ $latestActivityAt }}';
function updateActivityBadge(count){const badge=document.getElementById('activity-badge');if(!badge)return;badge.textContent=count>99?'99+':count;badge.classList.toggle('hidden',!count)}
function openActivityModal(){const m=document.getElementById('activity-history-modal');if(!m)return;m.classList.remove('hidden');m.classList.add('flex');bindAllActivityLongPress();}
function closeActivityModal(){const m=document.getElementById('activity-history-modal');if(!m)return;m.classList.add('hidden');m.classList.remove('flex');if(typeof exitSelectionMode==='function')exitSelectionMode();}
let activitySelectionMode=false;
function activityCheckbox(row){let box=row.querySelector('.activity-select');if(!box){box=document.createElement('input');box.type='checkbox';box.className='activity-select mt-1 h-4 w-4 shrink-0 rounded border-slate-300 text-emerald-600 cursor-pointer';box.addEventListener('click',e=>e.stopPropagation());row.querySelector('.flex')?.prepend(box)}return box}
function enterSelectionMode(targetRow){activitySelectionMode=true;document.querySelectorAll('.activity-entry').forEach(r=>{activityCheckbox(r).classList.remove('hidden');});if(targetRow)activityCheckbox(targetRow).checked=true;document.getElementById('activity-selection-actions')?.classList.remove('hidden');document.getElementById('activity-select-all-btn')?.classList.add('hidden');}
function exitSelectionMode(){activitySelectionMode=false;document.querySelectorAll('.activity-select').forEach(b=>{b.classList.add('hidden');b.checked=false});const allBox=document.getElementById('activity-select-all');if(allBox)allBox.checked=false;document.getElementById('activity-selection-actions')?.classList.add('hidden');document.getElementById('activity-select-all-btn')?.classList.remove('hidden');}
function bindActivityLongPress(row){if(row.dataset.longPressBound)return;row.dataset.longPressBound='1';let _t=null,_sx=0,_sy=0;function _start(x,y){_sx=x;_sy=y;_t=setTimeout(()=>{_t=null;if(navigator.vibrate)navigator.vibrate(50);row.dataset.justLongPressed='1';enterSelectionMode(row);},400);}function _cancel(){if(_t){clearTimeout(_t);_t=null;}}function _move(x,y){if(_t&&(Math.abs(x-_sx)>10||Math.abs(y-_sy)>10))_cancel();}row.addEventListener('mousedown',e=>{if(e.button!==0||e.target.closest('input,button'))return;_start(e.clientX,e.clientY);});row.addEventListener('mousemove',e=>_move(e.clientX,e.clientY));row.addEventListener('mouseup',_cancel);row.addEventListener('mouseleave',_cancel);row.addEventListener('touchstart',e=>{if(e.target.closest('input,button'))return;const t=e.touches[0];_start(t.clientX,t.clientY);},{passive:true});row.addEventListener('touchmove',e=>{const t=e.changedTouches[0];_move(t.clientX,t.clientY);},{passive:true});row.addEventListener('touchend',_cancel);row.addEventListener('touchcancel',_cancel);row.addEventListener('contextmenu',e=>{e.preventDefault();_cancel();row.dataset.justLongPressed='1';enterSelectionMode(row);});}
function bindAllActivityLongPress(){document.querySelectorAll('.activity-entry').forEach(bindActivityLongPress);}
async function deleteSelectedActivities(){const ids=[...document.querySelectorAll('.activity-select:checked')].map(b=>b.closest('[data-activity-id]').dataset.activityId);if(!ids.length||!await window.confirmDeletion('{{ __('Move the selected notifications to the trash?') }}'))return;const resp=await fetch('{{ route('activity-notifications.destroy') }}',{method:'DELETE',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Content-Type':'application/json'},body:JSON.stringify({ids})});if(!resp.ok){alert('{{ __('Action not allowed.') }}');return;}const result=await resp.json();(result.deleted_ids||[]).forEach(id=>document.querySelector(`[data-activity-id="${id}"]`)?.remove());exitSelectionMode();}
document.addEventListener('DOMContentLoaded',()=>{
  document.getElementById('activity-select-all')?.addEventListener('change',e=>document.querySelectorAll('.activity-select').forEach(b=>b.checked=e.target.checked));
  document.getElementById('activity-delete-btn')?.addEventListener('click',deleteSelectedActivities);
  document.getElementById('activity-cancel-select-btn')?.addEventListener('click',exitSelectionMode);
  document.getElementById('activity-select-all-btn')?.addEventListener('click',()=>{enterSelectionMode(null);document.querySelectorAll('.activity-select').forEach(b=>b.checked=true);const allBox=document.getElementById('activity-select-all');if(allBox)allBox.checked=true;});
  bindAllActivityLongPress();
});
const activityUrlMap=@json($activityNotifications->mapWithKeys(fn($activity)=>[$activity->id=>\App\Services\ActivityDestinationResolver::url($activity)]));
document.addEventListener('click',async event=>{const row=event.target.closest('.activity-entry');if(!row||event.target.closest('input,button,a'))return;if(row.dataset.justLongPressed==='1'){delete row.dataset.justLongPressed;event.preventDefault();event.stopPropagation();return;}if(activitySelectionMode){const box=activityCheckbox(row);box.checked=!box.checked;event.preventDefault();event.stopPropagation();return;}if(row.classList.contains('is-new')){await fetch('{{ route('activity-notifications.read') }}',{method:'POST',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content,'Content-Type':'application/json',Accept:'application/json'},body:JSON.stringify({id:Number(row.dataset.activityId)})});row.classList.remove('is-new','-mx-2','rounded-lg','bg-slate-100/70','px-2');row.querySelector('.activity-new-dot')?.classList.add('hidden');const badge=document.getElementById('activity-badge');updateActivityBadge(Math.max(0,Number(badge?.textContent||0)-1));}const url=row.dataset.activityUrl||activityUrlMap[row.dataset.activityId];if(url)window.location.href=url;});
</script>
@endif

