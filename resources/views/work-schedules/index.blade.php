<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold tracking-tight text-slate-800 dark:text-slate-100">{{ __('Work Schedules') }}</h2>
                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ __('Define the daily work time slots for permanent staff. Teachers are tracked per their course schedule.') }}</p>
            </div>
            <div class="flex items-center gap-2">
                {{-- Manual absence check trigger --}}
                @can('roles.manage')
                <button type="button" id="run-check-btn" onclick="openAbsenceCheckModal()"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-rose-100 hover:bg-rose-200 text-rose-700 border border-rose-200/80 dark:border-transparent dark:bg-rose-600 dark:text-white dark:hover:bg-rose-700 px-3.5 py-2 text-xs font-semibold shadow-sm transition active:scale-95">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    {{ __('Check Absences Now') }}
                </button>
                <button type="button" onclick="openCreateModal()"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-100 hover:bg-indigo-200 text-indigo-700 border border-indigo-200/80 dark:border-transparent dark:bg-indigo-600 dark:text-white dark:hover:bg-indigo-700 px-3.5 py-2 text-xs font-semibold shadow-sm transition active:scale-95">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    {{ __('Add Time Slot') }}
                </button>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 dark:bg-emerald-950/30 dark:border-emerald-800 px-4 py-3 flex items-center gap-3 text-sm text-emerald-800 dark:text-emerald-300">
                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ session('success') }}
            </div>
            @endif

            {{-- ─── Legend ─── --}}
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-800/40 shadow-sm p-5">
                <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 mb-3 flex items-center gap-2">
                    <svg class="h-4 w-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ __('How absence alerts work') }}
                </h3>
                <div class="grid sm:grid-cols-2 gap-4 text-xs text-slate-600 dark:text-slate-400">
                    <div class="flex gap-3 rounded-xl bg-indigo-50 dark:bg-indigo-950/30 border border-indigo-100 dark:border-indigo-900 p-3.5">
                        <div class="shrink-0 h-8 w-8 rounded-lg bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center">
                            <svg class="h-4 w-4 text-indigo-600 dark:text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                        <div>
                            <p class="font-bold text-indigo-800 dark:text-indigo-200 mb-0.5">{{ __('Permanent Staff') }}</p>
                            <p>{{ __('Must be present during each active work slot below. A biometric punch within 30 min of the slot start is accepted.') }}</p>
                        </div>
                    </div>
                    <div class="flex gap-3 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-100 dark:border-amber-900 p-3.5">
                        <div class="shrink-0 h-8 w-8 rounded-lg bg-amber-100 dark:bg-amber-900 flex items-center justify-center">
                            <svg class="h-4 w-4 text-amber-600 dark:text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        </div>
                        <div>
                            <p class="font-bold text-amber-800 dark:text-amber-200 mb-0.5">{{ __('Non-Permanent Staff (Teachers)') }}</p>
                            <p>{{ __('Must be present during each of their scheduled classes. Alert is sent if no punch is detected at lesson end.') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ─── Schedule Grid ─── --}}
            @php
                $days = [
                    null => __('Every Day'),
                    0 => __('Monday'), 1 => __('Tuesday'), 2 => __('Wednesday'),
                    3 => __('Thursday'), 4 => __('Friday'), 5 => __('Saturday'), 6 => __('Sunday'),
                ];
                $todayDow = now()->dayOfWeekIso - 1;
            @endphp

            @foreach ($days as $dow => $dayLabel)
                @php $daySlots = $schedules->filter(fn($s) => $s->day_of_week === $dow)->sortBy('starts_at'); @endphp
                @if ($daySlots->isNotEmpty() || $dow === null)
                <div class="rounded-2xl border {{ $dow === $todayDow ? 'border-indigo-300 dark:border-indigo-700 ring-1 ring-indigo-200 dark:ring-indigo-800' : 'border-slate-200 dark:border-slate-800' }} bg-white dark:bg-slate-800/40 shadow-sm overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-3 border-b border-slate-100 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-800/60">
                        <div class="flex items-center gap-2">
                            @if ($dow === $todayDow)
                                <span class="h-2 w-2 rounded-full bg-indigo-500 animate-pulse"></span>
                            @endif
                            <span class="text-sm font-bold text-slate-800 dark:text-slate-100">{{ $dayLabel }}</span>
                            @if ($dow === $todayDow)
                                <span class="rounded-full bg-indigo-100 dark:bg-indigo-900 px-2 py-0.5 text-[10px] font-bold text-indigo-700 dark:text-indigo-300">{{ __('Today') }}</span>
                            @endif
                        </div>
                        <span class="text-xs text-slate-400">{{ $daySlots->count() }} {{ __('slot(s)') }}</span>
                    </div>

                    @if ($daySlots->isEmpty())
                        <div class="px-5 py-6 text-center text-xs text-slate-400">
                            {{ __('No time slots defined for every day. Add one below.') }}
                        </div>
                    @else
                        <div class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach ($daySlots as $slot)
                            <div class="flex items-center gap-4 px-5 py-3.5 {{ $slot->is_active ? '' : 'opacity-50' }} group hover:bg-slate-50/60 dark:hover:bg-slate-800/60 transition">
                                {{-- Time pill --}}
                                <div class="flex items-center gap-2 min-w-[140px]">
                                    <svg class="h-3.5 w-3.5 text-indigo-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span class="text-sm font-bold text-slate-800 dark:text-slate-100 tabular-nums">
                                        {{ substr($slot->starts_at, 0, 5) }} – {{ substr($slot->ends_at, 0, 5) }}
                                    </span>
                                </div>

                                {{-- Name --}}
                                <span class="flex-1 text-sm text-slate-600 dark:text-slate-300 font-medium">{{ $slot->name }}</span>

                                <div class="flex items-center gap-2">
                                    {{-- Active toggle --}}
                                    <button type="button"
                                        onclick="toggleSlot({{ $slot->id }}, this)"
                                        class="shrink-0 rounded-full px-2.5 py-1 text-[11px] font-bold border transition
                                            {{ $slot->is_active
                                                ? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800'
                                                : 'bg-slate-100 text-slate-500 border-slate-200 dark:bg-slate-700 dark:text-slate-400 dark:border-slate-600' }}"
                                        data-active="{{ $slot->is_active ? '1' : '0' }}">
                                        {{ $slot->is_active ? __('Active') : __('Inactive') }}
                                    </button>

                                    {{-- Delete button --}}
                                    @can('roles.manage')
                                    <button type="button" onclick="openDeleteModal({{ $slot->id }})" class="rounded-lg p-1 text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:text-slate-500 dark:hover:text-rose-400 dark:hover:bg-rose-950/40 transition" title="{{ __('Delete') }}">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                    @endcan
                                </div>

                                {{-- Actions (Edit only) --}}
                                @can('roles.manage')
                                <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition">
                                    <button type="button" onclick='openEditModal(@json($slot))'
                                        class="rounded-lg p-1.5 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 transition">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                </div>
                                @endcan
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>
                @endif
            @endforeach

            {{-- Show days with no slots that user hasn't added yet -- shortcut --}}
            @if ($schedules->isEmpty())
            <div class="rounded-2xl border-2 border-dashed border-slate-200 dark:border-slate-700 p-10 text-center">
                <div class="mx-auto h-12 w-12 rounded-2xl bg-indigo-50 dark:bg-indigo-950/40 flex items-center justify-center mb-3">
                    <svg class="h-6 w-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <p class="text-sm font-bold text-slate-700 dark:text-slate-200">{{ __('No work schedules defined yet') }}</p>
                <p class="text-xs text-slate-400 mt-1">{{ __('Click "Add Time Slot" to create your first schedule.') }}</p>
                <button type="button" onclick="openCreateModal()" class="mt-4 inline-flex items-center gap-1.5 rounded-xl bg-indigo-100 dark:bg-indigo-600 px-4 py-2 text-xs font-semibold text-indigo-700 dark:text-white hover:bg-indigo-200 dark:hover:bg-indigo-700 transition">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    {{ __('Add Time Slot') }}
                </button>
            </div>
            @endif

        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    {{-- CREATE / EDIT MODAL                                                   --}}
    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    <div id="slot-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4" role="dialog" aria-modal="true">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeSlotModal()"></div>
        <div class="relative w-full max-w-md rounded-2xl bg-white dark:bg-slate-900 shadow-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">

            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-800 bg-gradient-to-r from-indigo-50 to-violet-50 dark:from-indigo-950/40 dark:to-violet-950/40">
                <div class="flex items-center gap-2">
                    <div class="h-8 w-8 rounded-lg bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center">
                        <svg class="h-4 w-4 text-indigo-600 dark:text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3 id="modal-title" class="text-sm font-bold text-slate-800 dark:text-slate-100">{{ __('Add Time Slot') }}</h3>
                </div>
                <button onclick="closeSlotModal()" class="rounded-lg p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form id="slot-form" method="POST" action="{{ route('work-schedules.store') }}" class="p-6 space-y-4">
                @csrf
                <span id="form-method-field"></span>

                {{-- Name --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ __('Slot Name') }}</label>
                    <input type="text" name="name" id="slot-name" required placeholder="{{ __('e.g. Morning, Afternoon…') }}"
                        class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3.5 py-2.5 text-sm text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-400 transition">
                </div>

                {{-- Day of week --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ __('Applies to') }}</label>
                    <select name="day_of_week" id="slot-day"
                        class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3.5 py-2.5 text-sm text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-400 transition">
                        <option value="">{{ __('Every working day') }}</option>
                        <option value="0">{{ __('Monday') }}</option>
                        <option value="1">{{ __('Tuesday') }}</option>
                        <option value="2">{{ __('Wednesday') }}</option>
                        <option value="3">{{ __('Thursday') }}</option>
                        <option value="4">{{ __('Friday') }}</option>
                        <option value="5">{{ __('Saturday') }}</option>
                        <option value="6">{{ __('Sunday') }}</option>
                    </select>
                </div>

                {{-- Times --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ __('Start') }}</label>
                        <input type="time" name="starts_at" id="slot-starts" required
                            class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3.5 py-2.5 text-sm text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-400 transition">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ __('End') }}</label>
                        <input type="time" name="ends_at" id="slot-ends" required
                            class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3.5 py-2.5 text-sm text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-400 transition">
                    </div>
                </div>

                {{-- Active --}}
                <div class="flex items-center gap-3">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" id="slot-active" value="1" checked
                        class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <label for="slot-active" class="text-sm text-slate-700 dark:text-slate-300 font-medium">{{ __('Active (used for absence checks)') }}</label>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="closeSlotModal()"
                        class="rounded-xl px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:text-slate-300 dark:hover:bg-slate-600 transition">
                        {{ __('Cancel') }}
                    </button>
                    <button type="submit"
                        class="rounded-xl px-5 py-2 text-xs font-semibold bg-indigo-100 dark:bg-indigo-600 text-indigo-700 dark:text-white hover:bg-indigo-200 dark:hover:bg-indigo-700 transition active:scale-95 shadow-sm">
                        <span id="modal-submit-label">{{ __('Create Slot') }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    {{-- DELETE MODAL                                                          --}}
    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    <div id="delete-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4" role="dialog" aria-modal="true">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeDeleteModal()"></div>
        <div class="relative w-full max-w-sm rounded-2xl bg-white dark:bg-slate-900 shadow-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="p-6 text-center">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-rose-100 dark:bg-rose-900/50 mb-4">
                    <svg class="h-6 w-6 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-2">{{ __('Delete Time Slot') }}</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('Are you sure you want to delete this time slot? This action cannot be undone.') }}</p>
                
                <form id="delete-form" method="POST" class="mt-6 flex justify-center gap-3">
                    @csrf @method('DELETE')
                    <button type="button" onclick="closeDeleteModal()" class="rounded-xl px-4 py-2 text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700 transition">
                        {{ __('Cancel') }}
                    </button>
                    <button type="submit" class="rounded-xl px-4 py-2 text-sm font-semibold text-white bg-rose-600 hover:bg-rose-700 transition shadow-sm active:scale-95">
                        {{ __('Delete') }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    {{-- ABSENCE CHECK MODAL                                                   --}}
    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    <div id="absence-check-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4" role="dialog" aria-modal="true">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeAbsenceCheckModal()"></div>
        <div class="relative w-full max-w-sm rounded-2xl bg-white dark:bg-slate-900 shadow-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="p-6 text-center">
                {{-- Icon --}}
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-rose-100 dark:bg-rose-900/50 mb-4" id="absence-modal-icon-container">
                    <svg id="absence-modal-icon-default" class="h-6 w-6 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    <svg id="absence-modal-icon-success" class="hidden h-6 w-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <svg id="absence-modal-icon-error" class="hidden h-6 w-6 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    <svg id="absence-modal-icon-loading" class="hidden h-6 w-6 text-rose-600 dark:text-rose-400 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                </div>
                
                <h3 id="absence-modal-title" class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-2">{{ __('Manual Absence Check') }}</h3>
                <p id="absence-modal-message" class="text-sm text-slate-500 dark:text-slate-400">{{ __('Are you sure you want to run the absence check now? This will scan for missing attendances based on current schedules.') }}</p>
                
                <div id="absence-modal-actions" class="mt-6 flex justify-center gap-3">
                    <button type="button" onclick="closeAbsenceCheckModal()" id="absence-modal-cancel-btn" class="rounded-xl px-4 py-2 text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700 transition">
                        {{ __('Cancel') }}
                    </button>
                    <button type="button" onclick="executeAbsenceCheck()" id="absence-modal-confirm-btn" class="rounded-xl px-4 py-2 text-sm font-semibold bg-rose-100 hover:bg-rose-200 text-rose-700 border border-rose-200/80 dark:border-transparent dark:bg-rose-600 dark:text-white dark:hover:bg-rose-700 transition shadow-sm active:scale-95">
                        {{ __('Run Check') }}
                    </button>
                </div>

                <div id="absence-modal-close-only" class="mt-6 hidden flex justify-center">
                    <button type="button" onclick="closeAbsenceCheckModal()" class="rounded-xl px-6 py-2 text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700 transition">
                        {{ __('Close') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const ROUTES = {
            store:     "{{ route('work-schedules.store') }}",
            toggle:    '/work-schedules/{id}/toggle',
            runCheck:  "{{ route('work-schedules.run-check') }}",
        };
        const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

        // ── Modal helpers ─────────────────────────────────────────────────────
        function openCreateModal() {
            document.getElementById('modal-title').textContent         = @json(__('Add Time Slot'));
            document.getElementById('modal-submit-label').textContent  = @json(__('Create Slot'));
            document.getElementById('slot-form').action               = ROUTES.store;
            document.getElementById('form-method-field').innerHTML    = '';
            document.getElementById('slot-name').value   = '';
            document.getElementById('slot-day').value    = '';
            document.getElementById('slot-starts').value = '';
            document.getElementById('slot-ends').value   = '';
            document.getElementById('slot-active').checked = true;
            openSlotModal();
        }

        function openEditModal(slot) {
            document.getElementById('modal-title').textContent         = @json(__('Edit Time Slot'));
            document.getElementById('modal-submit-label').textContent  = @json(__('Save Changes'));
            document.getElementById('slot-form').action = '/work-schedules/' + slot.id;
            document.getElementById('form-method-field').innerHTML = '<input type="hidden" name="_method" value="PUT">';
            document.getElementById('slot-name').value    = slot.name ?? '';
            document.getElementById('slot-day').value     = slot.day_of_week ?? '';
            document.getElementById('slot-starts').value  = (slot.starts_at ?? '').substring(0, 5);
            document.getElementById('slot-ends').value    = (slot.ends_at ?? '').substring(0, 5);
            document.getElementById('slot-active').checked = !!slot.is_active;
            openSlotModal();
        }

        function openSlotModal() {
            const m = document.getElementById('slot-modal');
            m.classList.remove('hidden');
            m.classList.add('flex');
            document.getElementById('slot-name').focus();
        }

        function closeSlotModal() {
            const m = document.getElementById('slot-modal');
            m.classList.add('hidden');
            m.classList.remove('flex');
        }

        // ── Delete modal ──────────────────────────────────────────────────────
        function openDeleteModal(id) {
            document.getElementById('delete-form').action = '/work-schedules/' + id;
            const m = document.getElementById('delete-modal');
            m.classList.remove('hidden');
            m.classList.add('flex');
        }

        function closeDeleteModal() {
            const m = document.getElementById('delete-modal');
            m.classList.add('hidden');
            m.classList.remove('flex');
        }

        // ── Toggle active via AJAX ────────────────────────────────────────────
        async function toggleSlot(id, btn) {
            const resp = await fetch('/work-schedules/' + id + '/toggle', {
                method: 'PATCH',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
            });
            if (!resp.ok) return;
            const data = await resp.json();
            const active = data.is_active;
            btn.dataset.active = active ? '1' : '0';
            btn.textContent    = active ? @json(__('Active')) : @json(__('Inactive'));
            btn.className = 'shrink-0 rounded-full px-2.5 py-1 text-[11px] font-bold border transition ' +
                (active
                    ? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800'
                    : 'bg-slate-100 text-slate-500 border-slate-200 dark:bg-slate-700 dark:text-slate-400 dark:border-slate-600');
            // Fade row
            btn.closest('div.flex').style.opacity = active ? '1' : '0.5';
        }

        // ── Absence Check Modal ───────────────────────────────────────────────
        function openAbsenceCheckModal() {
            document.getElementById('absence-modal-icon-default').classList.remove('hidden');
            document.getElementById('absence-modal-icon-success').classList.add('hidden');
            document.getElementById('absence-modal-icon-error').classList.add('hidden');
            document.getElementById('absence-modal-icon-loading').classList.add('hidden');
            document.getElementById('absence-modal-icon-container').className = 'mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-rose-100 dark:bg-rose-900/50 mb-4';
            
            document.getElementById('absence-modal-title').textContent = @json(__('Manual Absence Check'));
            document.getElementById('absence-modal-message').textContent = @json(__('Are you sure you want to run the absence check now? This will scan for missing attendances based on current schedules.'));
            
            document.getElementById('absence-modal-actions').classList.remove('hidden');
            document.getElementById('absence-modal-close-only').classList.add('hidden');

            const m = document.getElementById('absence-check-modal');
            m.classList.remove('hidden');
            m.classList.add('flex');
        }

        function closeAbsenceCheckModal() {
            const m = document.getElementById('absence-check-modal');
            m.classList.add('hidden');
            m.classList.remove('flex');
        }

        async function executeAbsenceCheck() {
            document.getElementById('absence-modal-icon-default').classList.add('hidden');
            document.getElementById('absence-modal-icon-loading').classList.remove('hidden');
            document.getElementById('absence-modal-title').textContent = @json(__('Checking…'));
            document.getElementById('absence-modal-message').textContent = @json(__('Please wait while the system checks for missing attendances.'));
            
            const confirmBtn = document.getElementById('absence-modal-confirm-btn');
            const cancelBtn = document.getElementById('absence-modal-cancel-btn');
            confirmBtn.disabled = true;
            cancelBtn.disabled = true;

            try {
                const resp = await fetch(ROUTES.runCheck, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
                });
                const data = await resp.json();
                const total = (data.alerts?.permanent ?? 0) + (data.alerts?.non_permanent ?? 0);
                
                document.getElementById('absence-modal-icon-loading').classList.add('hidden');
                document.getElementById('absence-modal-icon-success').classList.remove('hidden');
                document.getElementById('absence-modal-icon-container').className = 'mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-900/50 mb-4';
                
                document.getElementById('absence-modal-title').textContent = @json(__('Check Complete'));
                document.getElementById('absence-modal-message').textContent = total > 0
                    ? @json(__(':n absence alert(s) sent.')) .replace(':n', total)
                    : @json(__('No absences detected for the current time.'));
            } catch (e) {
                document.getElementById('absence-modal-icon-loading').classList.add('hidden');
                document.getElementById('absence-modal-icon-error').classList.remove('hidden');
                document.getElementById('absence-modal-icon-container').className = 'mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-rose-100 dark:bg-rose-900/50 mb-4';
                
                document.getElementById('absence-modal-title').textContent = @json(__('Error'));
                document.getElementById('absence-modal-message').textContent = @json(__('An error occurred. Please try again.'));
            } finally {
                confirmBtn.disabled = false;
                cancelBtn.disabled = false;
                document.getElementById('absence-modal-actions').classList.add('hidden');
                document.getElementById('absence-modal-close-only').classList.remove('hidden');
            }
        }

        // Close modal on Escape
        document.addEventListener('keydown', e => { 
            if (e.key === 'Escape') {
                closeSlotModal();
                closeDeleteModal();
                closeAbsenceCheckModal();
            }
        });
    </script>
</x-app-layout>
