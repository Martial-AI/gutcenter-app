<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold tracking-tight text-slate-800">{{ __('Attendance and Statistics') }}</h2>
                <p class="mt-0.5 text-xs text-slate-500">{{ __('Comprehensive analytics: attendance rates, financial forecasts, student distribution and biometric logs.') }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-2.5">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 border border-emerald-200/80 shadow-sm">
                    <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    {{ __('Biometric Terminal Active') }}
                </span>
                @can('attendance.manage')
                    <form id="sync-zkteco-form" method="POST" action="{{ route('attendance.sync-device') }}" class="inline-flex" onsubmit="handleSyncDevice(event, this)">
                        @csrf
                        <button type="submit" id="sync-zkteco-btn" class="inline-flex items-center gap-1.5 rounded-xl bg-slate-800 px-3.5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-slate-900 transition active:scale-95" title="{{ __('Connect to ZKTeco on local network') }}">
                            <svg id="sync-zkteco-icon" class="h-4 w-4 text-emerald-400 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span id="sync-zkteco-text">{{ __('Synchronize Fingerprint Scanner') }}</span>
                        </button>
                    </form>
                    <script>
                        function handleSyncDevice(event, form) {
                            event.preventDefault();
                            const btn = document.getElementById('sync-zkteco-btn');
                            const icon = document.getElementById('sync-zkteco-icon');
                            const text = document.getElementById('sync-zkteco-text');

                            if (icon) {
                                icon.classList.add('animate-spin', 'spinning');
                                icon.style.animation = 'spin 0.8s linear infinite';
                            }
                            if (btn) {
                                btn.disabled = true;
                                btn.classList.add('opacity-75', 'cursor-wait');
                            }
                            if (text) {
                                text.textContent = '{{ __('Synchronisation en cours...') }}';
                            }

                            const formData = new FormData(form);
                            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || form.querySelector('input[name="_token"]')?.value;

                            fetch(form.action, {
                                method: 'POST',
                                body: formData,
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken
                                }
                            })
                            .then(async (response) => {
                                const data = await response.json().catch(() => ({}));
                                // Synchronization finished successfully!
                                window.location.reload();
                            })
                            .catch((error) => {
                                console.error('Sync error:', error);
                                alert('{{ __('Erreur lors de la synchronisation avec la pointeuse.') }}');
                                if (icon) {
                                    icon.classList.remove('animate-spin', 'spinning');
                                    icon.style.animation = '';
                                }
                                if (btn) {
                                    btn.disabled = false;
                                    btn.classList.remove('opacity-75', 'cursor-wait');
                                }
                                if (text) {
                                    text.textContent = '{{ __('Synchronize Fingerprint Scanner') }}';
                                }
                            });
                        }
                    </script>
                    <button type="button" onclick="openManualAttendanceModal()" class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-3.5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 transition active:scale-95">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        {{ __('Manual Attendance Entry') }}
                    </button>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-6" x-data="{ mainSection: 'all' }">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Section Navigation Pills -->
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 pb-3">
                <div class="flex flex-wrap gap-2">
                    <button type="button" @click="mainSection = 'all'" :class="mainSection === 'all' ? 'bg-slate-900 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'" class="rounded-xl px-4 py-2 text-xs font-bold transition active:scale-95">
                        {{ __('Overview') }}
                    </button>
                    <button type="button" @click="mainSection = 'attendance'" :class="mainSection === 'attendance' ? 'bg-emerald-700 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'" class="rounded-xl px-4 py-2 text-xs font-bold transition active:scale-95">
                        {{ __('Attendance and Absences') }}
                    </button>
                    <button type="button" @click="mainSection = 'finance'" :class="mainSection === 'finance' ? 'bg-sky-700 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'" class="rounded-xl px-4 py-2 text-xs font-bold transition active:scale-95">
                        {{ __('Analytics and Financial Projections') }}
                    </button>
                </div>

                <div class="text-xs text-slate-500 font-medium">
                    {{ __('School Capacity') }}: <strong class="text-slate-800">{{ $studentCount }} / {{ $totalCapacity }} ({{ $capacityOccupancyRate }}%)</strong>
                </div>
            </div>

            <!-- Top Metric Cards Grid (6 Compact Cards) -->
            <div class="grid gap-3.5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
                <!-- Card 1: Student Presence -->
                <div class="relative overflow-hidden rounded-xl bg-gradient-to-br from-emerald-600 to-teal-700 p-4 text-white shadow-sm transition hover:-translate-y-0.5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-emerald-100/90">{{ __('Student Presence Rate') }}</span>
                        <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-white/15">
                            <svg class="h-3.5 w-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                    <p class="mt-2 text-2xl font-black tracking-tight">{{ $studentPresenceRate }}%</p>
                    <div class="mt-1 text-[11px] text-emerald-100/80">
                        <span>{{ $studentAbsencesCount }} {{ __('Student Absences') }}</span>
                    </div>
                </div>

                <!-- Card 2: Student Absence -->
                <div class="rounded-xl bg-white p-4 border border-slate-200/70 shadow-sm transition hover:-translate-y-0.5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-slate-500">{{ __('Student Absence Rate') }}</span>
                        <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-50 text-amber-600">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                    <p class="mt-2 text-2xl font-black tracking-tight text-slate-800">{{ $studentAbsenceRate }}%</p>
                    <div class="mt-1 text-[11px] text-amber-600 font-semibold">
                        <span>{{ $flaggedStudents->count() }} {{ __('Flagged Students') }}</span>
                    </div>
                </div>

                <!-- Card 3: Staff Presence -->
                <div class="rounded-xl bg-white p-4 border border-slate-200/70 shadow-sm transition hover:-translate-y-0.5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-slate-500">{{ __('Staff Presence Rate') }}</span>
                        <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-sky-50 text-sky-600">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                    </div>
                    <p class="mt-2 text-2xl font-black tracking-tight text-slate-800">{{ $staffPresenceRate }}%</p>
                    <div class="mt-1 text-[11px] text-slate-400">
                        <span>{{ $staffAbsencesCount }} {{ __('Staff Absences') }}</span>
                    </div>
                </div>

                <!-- Card 4: Teacher Alerts -->
                <div class="rounded-xl bg-white p-4 border border-rose-200/80 shadow-sm transition hover:-translate-y-0.5 bg-gradient-to-br from-white to-rose-50/30">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-rose-700">{{ __('Teacher and Staff Alerts') }}</span>
                        <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-rose-100 text-rose-700">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0 1 18 14.158V11a6 6 0 0 0-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 0 1-6 0v-1m6 0H9"/></svg>
                        </div>
                    </div>
                    <p class="mt-2 text-2xl font-black tracking-tight text-rose-700">{{ $flaggedStaff->count() }}</p>
                    <div class="mt-1 text-[11px] text-rose-600 font-medium">
                        <span>{{ __('Absent Teachers/Staff') }}</span>
                    </div>
                </div>

                <!-- Card 5: Enrolled Students -->
                <div class="rounded-xl bg-white p-4 border border-slate-200/70 shadow-sm transition hover:-translate-y-0.5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-slate-500">{{ __('Enrolled Students') }}</span>
                        <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/></svg>
                        </div>
                    </div>
                    <p class="mt-2 text-2xl font-black tracking-tight text-slate-800">{{ $studentCount }}</p>
                    <div class="mt-1 text-[11px] text-slate-400">
                        <span>{{ $capacityOccupancyRate }}% {{ __('of capacity') }}</span>
                    </div>
                </div>

                <!-- Card 6: Total Classes -->
                <div class="rounded-xl bg-white p-4 border border-slate-200/70 shadow-sm transition hover:-translate-y-0.5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-slate-500">{{ __('Total Registered Classes') }}</span>
                        <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-purple-50 text-purple-600">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        </div>
                    </div>
                    <p class="mt-2 text-2xl font-black tracking-tight text-slate-800">{{ $classes->count() }}</p>
                    <div class="mt-1 text-[11px] text-purple-700 font-semibold">
                        <span>{{ $teacherCount }} {{ __('Teachers') }}</span>
                    </div>
                </div>
            </div>

            <!-- SECTION 1: PRESENCE & ABSENCES -->
            <div x-show="mainSection === 'all' || mainSection === 'attendance'" class="space-y-6">

                <!-- Filter Bar -->
                <div class="rounded-2xl bg-white p-4 border border-slate-200/70 shadow-sm">
                    <form method="GET" action="{{ route('attendance.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 items-end">
                        <div>
                            <label for="class_id" class="block text-xs font-semibold text-slate-600 mb-1">{{ __('Filter by Class') }}</label>
                            <select id="class_id" name="class_id" class="w-full rounded-xl border-slate-200 text-xs text-slate-800 focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="">{{ __('All classes') }}</option>
                                @foreach($classes as $c)
                                    <option value="{{ $c->id }}" {{ (string)$selectedClassId === (string)$c->id ? 'selected' : '' }}>
                                        {{ $c->name }} ({{ $c->enrollments_count }} {{ __('Students') }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="date_from" class="block text-xs font-semibold text-slate-600 mb-1">{{ __('Start Date') }}</label>
                            <input type="date" id="date_from" name="date_from" value="{{ request('date_from') }}" class="w-full rounded-xl border-slate-200 text-xs text-slate-800 focus:border-emerald-500 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label for="date_to" class="block text-xs font-semibold text-slate-600 mb-1">{{ __('End Date') }}</label>
                            <input type="date" id="date_to" name="date_to" value="{{ request('date_to') }}" class="w-full rounded-xl border-slate-200 text-xs text-slate-800 focus:border-emerald-500 focus:ring-emerald-500">
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="submit" class="flex-1 rounded-xl bg-slate-800 py-2.5 px-4 text-xs font-semibold text-white hover:bg-slate-900 transition shadow-sm active:scale-95">
                                {{ __('Filter') }}
                            </button>
                            @if(request('class_id') || request('date_from') || request('date_to'))
                                <a href="{{ route('attendance.index') }}" class="rounded-xl border border-slate-300 py-2.5 px-3 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition active:scale-95" title="{{ __('Reset') }}">
                                    {{ __('Reset') }}
                                </a>
                            @endif
                        </div>
                    </form>
                </div>

                <!-- Alerts Grid (Flagged Students & Flagged Teachers) -->
                <div class="grid gap-6 lg:grid-cols-2">
                    <!-- Flagged Students Section (Sup 3 Absences) -->
                    <div class="rounded-2xl bg-white border border-amber-200/90 shadow-sm overflow-hidden flex flex-col justify-between">
                        <div class="border-b border-amber-100 bg-amber-50/50 p-4 flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-amber-100 text-amber-700 font-bold">
                                    !
                                </div>
                                <div>
                                    <h3 class="text-sm font-bold text-amber-950">{{ __('Flagged Students (Sup 3 Absences)') }}</h3>
                                    <p class="text-xs text-amber-700/80">{{ __('Students requiring immediate follow-up due to repeated absences.') }}</p>
                                </div>
                            </div>
                            <span class="rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-bold text-amber-800 border border-amber-200">
                                {{ $flaggedStudents->count() }}
                            </span>
                        </div>

                        <div class="divide-y divide-slate-100 max-h-80 overflow-y-auto p-2">
                            @forelse($flaggedStudents as $item)
                                <div class="p-3 hover:bg-amber-50/40 rounded-xl transition flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="h-10 w-10 shrink-0 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-700 font-bold overflow-hidden">
                                            @if($item['student']->photo_path)
                                                <img src="{{ route('students.photo', $item['student']) }}" alt="{{ $item['student']->first_name }}" class="h-full w-full object-cover">
                                            @else
                                                {{ strtoupper(substr($item['student']->first_name, 0, 1)) }}
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-xs font-bold text-slate-900 truncate">{{ $item['student']->first_name }} {{ $item['student']->last_name }}</p>
                                            <p class="text-[11px] text-slate-500">{{ $item['class_name'] }} · <span class="font-semibold text-amber-700">{{ $item['total_absences'] }} absences</span></p>
                                        </div>
                                    </div>
                                    <button type="button" onclick="showPersonDetails('student', {{ $item['student']->id }})" class="shrink-0 inline-flex items-center gap-1 rounded-lg bg-amber-100 px-2.5 py-1.5 text-xs font-semibold text-amber-900 hover:bg-amber-200 transition active:scale-95">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        {{ __('Follow this student') }}
                                    </button>
                                </div>
                            @empty
                                <div class="py-8 text-center text-xs text-slate-400">
                                    {{ __('No flagged students at this time.') }}
                                </div>
                            @endforelse
                        </div>

                        <div class="border-t border-slate-100 bg-slate-50/50 px-4 py-2.5 text-[11px] text-slate-500 flex items-center justify-between">
                            <span>{{ __('Auto-Alert rule') }}: <strong>3+ absences = Notification Admin</strong></span>
                            <span class="text-amber-700 font-medium">{{ __('Action required') }}</span>
                        </div>
                    </div>

                    <!-- Flagged Teachers/Staff Section -->
                    <div class="rounded-2xl bg-white border border-rose-200/90 shadow-sm overflow-hidden flex flex-col justify-between">
                        <div class="border-b border-rose-100 bg-rose-50/50 p-4 flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-rose-100 text-rose-700 font-bold">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                </div>
                                <div>
                                    <h3 class="text-sm font-bold text-rose-950">{{ __('Teacher and Staff Absence Alerts') }}</h3>
                                    <p class="text-xs text-rose-700/80">{{ __('Teachers or staff members absent recently.') }}</p>
                                </div>
                            </div>
                            <span class="rounded-full bg-rose-100 px-2.5 py-0.5 text-xs font-bold text-rose-800 border border-rose-200">
                                {{ $flaggedStaff->count() }}
                            </span>
                        </div>

                        <div class="divide-y divide-slate-100 max-h-80 overflow-y-auto p-2">
                            @forelse($flaggedStaff as $item)
                                <div class="p-3 hover:bg-rose-50/40 rounded-xl transition flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="h-10 w-10 shrink-0 rounded-full bg-rose-100 text-rose-800 font-bold flex items-center justify-center border border-rose-200">
                                            {{ strtoupper(substr($item['user']->name ?? 'P', 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-xs font-bold text-slate-900 truncate">{{ $item['user']->name }}</p>
                                            <p class="text-[11px] text-slate-500">{{ $item['user']->getRoleNames()->first() ?? 'Prof' }} · <span class="font-semibold text-rose-700">{{ $item['total_absences'] }} {{ __('Absences') }}</span></p>
                                        </div>
                                    </div>
                                    <button type="button" onclick="showPersonDetails('staff', {{ $item['user']->id }})" class="shrink-0 inline-flex items-center gap-1 rounded-lg bg-rose-100 px-2.5 py-1.5 text-xs font-semibold text-rose-900 hover:bg-rose-200 transition active:scale-95">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        {{ __('View Breakdown') }}
                                    </button>
                                </div>
                            @empty
                                <div class="py-8 text-center text-xs text-slate-400">
                                    {{ __('No absent staff records found.') }}
                                </div>
                            @endforelse
                        </div>

                        <div class="border-t border-slate-100 bg-slate-50/50 px-4 py-2.5 text-[11px] text-slate-500 flex items-center justify-between">
                            <span>{{ __('Notification Policy') }}: <strong>Alerte immédiate pour chaque absence de professeur</strong></span>
                            <span class="text-rose-700 font-medium">{{ __('Statut Pédagogique') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Absence Chart per Class -->
                <div class="rounded-2xl bg-white p-5 border border-slate-200/70 shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
                        <div>
                            <h3 class="text-sm font-bold text-slate-800">{{ __('Absence Rate by Class') }}</h3>
                            <p class="text-xs text-slate-500">{{ __('Attendance vs Absence frequency across all registered classes.') }}</p>
                        </div>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                            {{ $classStats->count() }} {{ __('Classes') }}
                        </span>
                    </div>

                    <div class="h-64 w-full">
                        <canvas id="classAbsenceChart"></canvas>
                    </div>
                </div>

                <!-- Tabbed Detailed Tables -->
                <div x-data="{ activeTab: 'students' }" class="rounded-2xl bg-white border border-slate-200/70 shadow-sm overflow-hidden">
                    <div class="border-b border-slate-200/80 bg-slate-50/60 px-4 pt-3 flex flex-wrap gap-2">
                        <button type="button" @click="activeTab = 'students'" :class="activeTab === 'students' ? 'border-emerald-600 text-emerald-800 bg-white font-bold' : 'border-transparent text-slate-500 hover:text-slate-700'" class="border-b-2 py-2.5 px-4 text-xs transition">
                            {{ __('Students Attendance List') }} ({{ $studentsList->count() }})
                        </button>
                        <button type="button" @click="activeTab = 'staff'" :class="activeTab === 'staff' ? 'border-emerald-600 text-emerald-800 bg-white font-bold' : 'border-transparent text-slate-500 hover:text-slate-700'" class="border-b-2 py-2.5 px-4 text-xs transition">
                            {{ __('Staff & Teachers Attendance List') }} ({{ $staffList->count() }})
                        </button>
                        <button type="button" @click="activeTab = 'biometric'" :class="activeTab === 'biometric' ? 'border-emerald-600 text-emerald-800 bg-white font-bold' : 'border-transparent text-slate-500 hover:text-slate-700'" class="border-b-2 py-2.5 px-4 text-xs transition">
                            {{ __('Biometric Punch Logs') }} ({{ $biometricLogs->count() }})
                        </button>
                    </div>

                    <!-- Tab 1: Students Table -->
                    <div x-show="activeTab === 'students'" class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-slate-700">
                            <thead class="border-b border-slate-200 bg-slate-50/80 text-[11px] uppercase font-bold text-slate-500">
                                <tr>
                                    <th class="py-3 px-4">{{ __('Student') }}</th>
                                    <th class="py-3 px-4">{{ __('Matricule') }}</th>
                                    <th class="py-3 px-4">{{ __('Class') }}</th>
                                    <th class="py-3 px-4 text-center">{{ __('Total Sessions') }}</th>
                                    <th class="py-3 px-4 text-center">{{ __('Total Absences') }}</th>
                                    <th class="py-3 px-4">{{ __('Absence Rate') }}</th>
                                    <th class="py-3 px-4 text-center">{{ __('Status') }}</th>
                                    <th class="py-3 px-4 text-right">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($studentsList as $stu)
                                    <tr class="hover:bg-slate-50/70 transition">
                                        <td class="py-3 px-4 font-semibold text-slate-900 flex items-center gap-2.5">
                                            <div class="h-8 w-8 shrink-0 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center font-bold text-slate-700 overflow-hidden text-xs">
                                                @if($stu['photo_url'])
                                                    <img src="{{ $stu['photo_url'] }}" alt="{{ $stu['name'] }}" class="h-full w-full object-cover">
                                                @else
                                                    {{ strtoupper(substr($stu['name'], 0, 1)) }}
                                                @endif
                                            </div>
                                            <span>{{ $stu['name'] }}</span>
                                        </td>
                                        <td class="py-3 px-4 text-slate-500 font-mono">{{ $stu['student_number'] }}</td>
                                        <td class="py-3 px-4 font-medium text-slate-700">{{ $stu['class_name'] }}</td>
                                        <td class="py-3 px-4 text-center font-semibold">{{ $stu['total_sessions'] }}</td>
                                        <td class="py-3 px-4 text-center">
                                            <span class="inline-flex items-center justify-center rounded-lg {{ $stu['absences'] >= 3 ? 'bg-amber-100 text-amber-800 font-bold' : ($stu['absences'] > 0 ? 'bg-slate-100 text-slate-700' : 'bg-emerald-50 text-emerald-700') }} px-2 py-0.5 text-xs">
                                                {{ $stu['absences'] }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4">
                                            <div class="flex items-center gap-2">
                                                <div class="h-1.5 w-16 overflow-hidden rounded-full bg-slate-100">
                                                    <div class="h-full rounded-full {{ $stu['absence_rate'] >= 30 ? 'bg-rose-500' : ($stu['absence_rate'] > 0 ? 'bg-amber-500' : 'bg-emerald-500') }}" style="width: {{ min(100, $stu['absence_rate']) }}%"></div>
                                                </div>
                                                <span class="text-[11px] font-semibold text-slate-600">{{ $stu['absence_rate'] }}%</span>
                                            </div>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            @if($stu['is_flagged'])
                                                <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-0.5 text-[10px] font-bold text-amber-800">
                                                    ! {{ __('Follow this student') }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700">
                                                    {{ __('Normal') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-right">
                                            <button type="button" onclick="showPersonDetails('student', {{ $stu['id'] }})" class="rounded-lg border border-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100 transition active:scale-95">
                                                {{ __('View Breakdown') }}
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="py-8 text-center text-xs text-slate-400">
                                            {{ __('No student records found.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Tab 2: Staff Table -->
                    <div x-show="activeTab === 'staff'" class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-slate-700">
                            <thead class="border-b border-slate-200 bg-slate-50/80 text-[11px] uppercase font-bold text-slate-500">
                                <tr>
                                    <th class="py-3 px-4">{{ __('Staff / Teacher') }}</th>
                                    <th class="py-3 px-4">{{ __('Professional No.') }}</th>
                                    <th class="py-3 px-4">{{ __('Role') }}</th>
                                    <th class="py-3 px-4 text-center">{{ __('Total Sessions') }}</th>
                                    <th class="py-3 px-4 text-center">{{ __('Total Absences') }}</th>
                                    <th class="py-3 px-4">{{ __('Absence Rate') }}</th>
                                    <th class="py-3 px-4 text-center">{{ __('Status') }}</th>
                                    <th class="py-3 px-4 text-right">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($staffList as $stf)
                                    <tr class="hover:bg-slate-50/70 transition">
                                        <td class="py-3 px-4 font-semibold text-slate-900 flex items-center gap-2.5">
                                            <div class="h-8 w-8 shrink-0 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center font-bold text-slate-700 text-xs">
                                                {{ strtoupper(substr($stf['name'], 0, 1)) }}
                                            </div>
                                            <span>{{ $stf['name'] }}</span>
                                        </td>
                                        <td class="py-3 px-4 text-slate-500 font-mono">{{ $stf['professional_number'] }}</td>
                                        <td class="py-3 px-4 font-medium text-slate-700">
                                            <span class="inline-flex items-center rounded-lg bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-700">
                                                {{ $stf['role'] }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-center font-semibold">{{ $stf['total_sessions'] }}</td>
                                        <td class="py-3 px-4 text-center">
                                            <span class="inline-flex items-center justify-center rounded-lg {{ $stf['absences'] > 0 ? 'bg-rose-100 text-rose-800 font-bold' : 'bg-emerald-50 text-emerald-700' }} px-2 py-0.5 text-xs">
                                                {{ $stf['absences'] }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4">
                                            <div class="flex items-center gap-2">
                                                <div class="h-1.5 w-16 overflow-hidden rounded-full bg-slate-100">
                                                    <div class="h-full rounded-full {{ $stf['absence_rate'] > 0 ? 'bg-rose-500' : 'bg-emerald-500' }}" style="width: {{ min(100, $stf['absence_rate']) }}%"></div>
                                                </div>
                                                <span class="text-[11px] font-semibold text-slate-600">{{ $stf['absence_rate'] }}%</span>
                                            </div>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            @if($stf['is_flagged'])
                                                <span class="inline-flex items-center gap-1 rounded-full bg-rose-100 px-2.5 py-0.5 text-[10px] font-bold text-rose-800">
                                                    {{ __('Absent') }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700">
                                                    {{ __('Normal') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-right">
                                            <button type="button" onclick="showPersonDetails('staff', {{ $stf['id'] }})" class="rounded-lg border border-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100 transition active:scale-95">
                                                {{ __('View Breakdown') }}
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="py-8 text-center text-xs text-slate-400">
                                            {{ __('No staff records found.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Tab 3: Biometric Punch Logs -->
                    <div x-show="activeTab === 'biometric'" class="p-4 space-y-4">
                        <div class="rounded-xl bg-slate-50 p-4 border border-slate-200/80 flex flex-wrap items-center justify-between gap-3 text-xs text-slate-600">
                            <div>
                                <span class="font-bold text-slate-800">{{ __('Fingerprint Scanner Endpoint') }}:</span>
                                <code class="ml-1.5 rounded bg-white px-2 py-0.5 font-mono text-emerald-700 border border-slate-200">POST /api/biometric/punch</code>
                            </div>
                            <p class="text-[11px] text-slate-500">{{ __('The biometric fingerprint device on the same local network sends attendance data automatically to this API.') }}</p>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs text-slate-700">
                                <thead class="border-b border-slate-200 bg-slate-50/80 text-[11px] uppercase font-bold text-slate-500">
                                    <tr>
                                        <th class="py-3 px-4">{{ __('Device Terminal') }}</th>
                                        <th class="py-3 px-4">{{ __('Identifier / ID') }}</th>
                                        <th class="py-3 px-4">{{ __('Date & Time') }}</th>
                                        <th class="py-3 px-4">{{ __('Status') }}</th>
                                        <th class="py-3 px-4">{{ __('Details') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @forelse($biometricLogs as $log)
                                        <tr class="hover:bg-slate-50/70 transition">
                                            <td class="py-3 px-4 font-semibold text-slate-900 font-mono">{{ $log->device_identifier }}</td>
                                            <td class="py-3 px-4 font-mono font-medium text-emerald-800">{{ $log->external_identifier }}</td>
                                            <td class="py-3 px-4 text-slate-500">{{ $log->occurred_at?->format('d/m/Y H:i:s') ?? $log->created_at->format('d/m/Y H:i:s') }}</td>
                                            <td class="py-3 px-4">
                                                @if($log->status === 'processed')
                                                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700">
                                                        {{ __('Processed') }}
                                                    </span>
                                                @elseif($log->status === 'failed')
                                                    <span class="inline-flex items-center rounded-full bg-rose-50 px-2 py-0.5 text-[10px] font-semibold text-rose-700">
                                                        {{ __('Failed') }}
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-700">
                                                        {{ $log->status }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="py-3 px-4 text-slate-500 text-[11px] truncate max-w-xs">{{ $log->error_message ?? __('Pointage biométrique reçu') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="py-8 text-center text-xs text-slate-400">
                                                {{ __('No biometric logs recorded yet.') }}
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>

            <!-- SECTION 2: ANALYTICS & PROJECTIONS FINANCIÈRES / EFFECTIFS -->
            <div x-show="mainSection === 'all' || mainSection === 'finance'" class="space-y-6 pt-2">
                <div class="flex items-center justify-between border-b border-slate-200/80 pb-2">
                    <div>
                        <h3 class="text-base font-bold text-slate-800">{{ __('Analytics & Financial Projections') }}</h3>
                        <p class="text-xs text-slate-500">{{ __('Bar charts & circular metrics of future budgets and student distribution.') }}</p>
                    </div>
                </div>

                <!-- 1. Diagramme en bâtons - Budgets & Projections Financières -->
                <div class="rounded-2xl bg-white p-5 border border-slate-200/70 shadow-sm transition hover:shadow-md">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4">
                        <div>
                            <h4 class="text-sm font-bold text-slate-800">{{ __('Budget & Financial Forecasts (12 Months)') }}</h4>
                            <p class="text-xs text-slate-500">{{ __('Historical revenue & expenses vs projected future monthly net balance.') }}</p>
                        </div>
                        <div class="flex items-center gap-3 text-xs">
                            <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm bg-emerald-500"></span> {{ __('Revenues') }}</span>
                            <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm bg-rose-500"></span> {{ __('Expenses') }}</span>
                            <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-sky-500"></span> {{ __('Net Balance Line') }}</span>
                        </div>
                    </div>
                    
                    <div class="h-60 w-full">
                        <canvas id="financialForecastChart"></canvas>
                    </div>
                </div>

                <!-- 2. Dual Grid: Donut Chart & Line Growth Chart -->
                <div class="grid gap-6 lg:grid-cols-2">
                    <!-- Left: Donut Chart - Répartition des Élèves par Classe -->
                    <div class="rounded-2xl bg-white p-5 border border-slate-200/70 shadow-sm transition hover:shadow-md flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <h4 class="text-sm font-bold text-slate-800">{{ __('Student Distribution by Class') }}</h4>
                                    <p class="text-xs text-slate-500">{{ __('Breakdown of active students across active classes.') }}</p>
                                </div>
                                <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-600">{{ $classes->count() }} {{ __('Classes') }}</span>
                            </div>

                            @if($classDistribution->isNotEmpty())
                                <div class="relative h-56 w-full flex items-center justify-center my-1">
                                    <canvas id="classDistributionDonutChart"></canvas>
                                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none text-center">
                                        <span class="text-xl font-extrabold text-slate-800">{{ $studentCount }}</span>
                                        <span class="text-[10px] font-medium text-slate-400 uppercase tracking-wider">{{ __('Students') }}</span>
                                    </div>
                                </div>
                            @else
                                <div class="flex h-56 items-center justify-center text-xs text-slate-400">
                                    {{ __('No class distribution data available yet.') }}
                                </div>
                            @endif
                        </div>
                        
                        <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                            <span>{{ __('Total Registered Classes') }}: <strong>{{ $classes->count() }}</strong></span>
                            <span>{{ __('Total Occupancy') }}: <strong>{{ $capacityOccupancyRate }}%</strong></span>
                        </div>
                    </div>

                    <!-- Right: Line Chart - Projections d'Évolution des Effectifs -->
                    <div class="rounded-2xl bg-white p-5 border border-slate-200/70 shadow-sm transition hover:shadow-md flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <h4 class="text-sm font-bold text-slate-800">{{ __('Multi-Year Growth & Capacity Forecast') }}</h4>
                                    <p class="text-xs text-slate-500">{{ __('Historical student numbers & 3-year capacity projections.') }}</p>
                                </div>
                                <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 border border-emerald-200/60">{{ __('Target Growth') }}</span>
                            </div>

                            <div class="h-56 w-full my-1">
                                <canvas id="growthProjectionChart"></canvas>
                            </div>
                        </div>

                        <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                            <span>{{ __('School Capacity') }}: <strong>{{ $totalCapacity }}</strong></span>
                            <span class="text-emerald-700 font-semibold">{{ __('Sustainable Expansion') }}</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Details Modal (Ajax per Person) -->
    <div id="details-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4 backdrop-blur-sm">
        <div class="flex max-h-[85vh] w-full max-w-2xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4 bg-slate-50/70">
                <div class="flex items-center gap-3">
                    <div id="modal-person-avatar" class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 text-emerald-800 font-bold text-sm">
                        ?
                    </div>
                    <div>
                        <h2 id="modal-person-name" class="font-bold text-slate-900 text-base">Chargement...</h2>
                        <p id="modal-person-sub" class="text-xs text-slate-500">...</p>
                    </div>
                </div>
                <button type="button" onclick="closeDetailsModal()" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Modal Stats Strip -->
            <div class="grid grid-cols-3 divide-x divide-slate-100 border-b border-slate-100 bg-white py-3 text-center text-xs">
                <div>
                    <span class="block text-slate-400 text-[10px] uppercase font-bold">{{ __('Total Absences') }}</span>
                    <strong id="modal-total-absences" class="text-lg font-black text-rose-600">0</strong>
                </div>
                <div>
                    <span class="block text-slate-400 text-[10px] uppercase font-bold">{{ __('Late') }}</span>
                    <strong id="modal-total-late" class="text-lg font-black text-amber-600">0</strong>
                </div>
                <div>
                    <span class="block text-slate-400 text-[10px] uppercase font-bold">{{ __('Present') }}</span>
                    <strong id="modal-total-present" class="text-lg font-black text-emerald-600">0</strong>
                </div>
            </div>

            <!-- Modal Session History List -->
            <div class="overflow-y-auto px-6 py-4 flex-1">
                <h4 class="text-xs font-bold uppercase text-slate-500 tracking-wider mb-3">{{ __('Absence details by course and date') }}</h4>
                <div id="modal-records-container" class="space-y-2">
                    <!-- Populated dynamically via JS -->
                </div>
            </div>

            <div class="border-t border-slate-100 px-6 py-3 bg-slate-50/50 flex justify-end">
                <button type="button" onclick="closeDetailsModal()" class="rounded-xl bg-slate-800 px-4 py-2 text-xs font-semibold text-white hover:bg-slate-900 transition active:scale-95">
                    {{ __('Close') }}
                </button>
            </div>
        </div>
    </div>

    <!-- Manual Attendance Entry Modal -->
    @can('attendance.manage')
    <div id="manual-attendance-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4 backdrop-blur-sm">
        <div class="flex max-h-[90vh] w-full max-w-xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl">
            <form method="POST" action="{{ route('attendance.store') }}">
                @csrf
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4 bg-emerald-50/60">
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm">{{ __('Manual Attendance Entry') }}</h3>
                        <p class="text-xs text-emerald-700/80">{{ __('Record or edit attendance session for a class or staff.') }}</p>
                    </div>
                    <button type="button" onclick="closeManualAttendanceModal()" class="rounded-lg p-1.5 text-slate-400 hover:bg-white transition">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="p-6 space-y-4 overflow-y-auto max-h-[65vh]">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                {{ __('Date') }}
                            </label>
                            <input type="date" name="attendance_date" value="{{ date('Y-m-d') }}" required class="w-full rounded-xl border-slate-200 dark:border-slate-700 text-xs text-slate-800 dark:text-slate-100 bg-white dark:bg-slate-800 focus:border-emerald-500 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                {{ __('Heure') }}
                            </label>
                            <input type="time" id="manual_attendance_time" name="attendance_time" value="{{ date('H:i') }}" required onchange="syncManualAttendanceTime(this.value)" class="w-full rounded-xl border-slate-200 dark:border-slate-700 text-xs text-slate-800 dark:text-slate-100 bg-white dark:bg-slate-800 focus:border-emerald-500 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                {{ __('Target') }}
                            </label>
                            <select id="manual_target_type" name="target_type" onchange="toggleManualTarget()" required class="w-full rounded-xl border-slate-200 dark:border-slate-700 text-xs text-slate-800 dark:text-slate-100 bg-white dark:bg-slate-800 focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="student">{{ __('Students') }}</option>
                                <option value="staff">{{ __('Personnel & Professeurs') }}</option>
                            </select>
                        </div>
                    </div>

                    <div id="manual-student-fields" class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">{{ __('Class') }}</label>
                            <select name="school_class_id" class="w-full rounded-xl border-slate-200 text-xs text-slate-800 focus:border-emerald-500 focus:ring-emerald-500">
                                @foreach($classes as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">{{ __('Subject') }}</label>
                            <select name="subject_id" class="w-full rounded-xl border-slate-200 text-xs text-slate-800 focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="">{{ __('General / Session complète') }}</option>
                                @foreach($subjects as $sub)
                                    <option value="{{ $sub->id }}">{{ $sub->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="border-t border-slate-100 dark:border-slate-800 pt-3">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-xs font-semibold text-slate-700 dark:text-slate-200">{{ __('Sélectionnez les personnes et leur statut') }} :</p>
                            <span class="text-[11px] text-slate-400 dark:text-slate-400">{{ __('Heure par défaut synchronisée') }}</span>
                        </div>
                        <div id="manual-people-list" class="space-y-2 max-h-52 overflow-y-auto pr-1">
                            @foreach($studentsList->take(30) as $stu)
                                <div class="manual-person-item manual-student-item flex items-center justify-between p-2 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-700/60 text-xs">
                                    <div class="min-w-0 pr-2">
                                        <p class="font-medium text-slate-800 dark:text-slate-100 truncate">{{ $stu['name'] }}</p>
                                        <p class="text-[10px] text-slate-400">{{ $stu['class_name'] }}</p>
                                    </div>
                                    <input type="hidden" name="attendances[{{ $loop->index }}][id]" value="{{ $stu['id'] }}">
                                    <div class="flex items-center gap-1.5 shrink-0">
                                        <input type="time" name="attendances[{{ $loop->index }}][time]" value="{{ date('H:i') }}" class="manual-row-time rounded-lg border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs py-1 px-1.5 w-20" title="{{ __('Heure') }}">
                                        <select name="attendances[{{ $loop->index }}][status]" class="rounded-lg border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 text-xs py-1 px-2">
                                            <option value="present">{{ __('Present') }}</option>
                                            <option value="absent">{{ __('Absent') }}</option>
                                            <option value="late">{{ __('Late') }}</option>
                                            <option value="excused">{{ __('Excused') }}</option>
                                        </select>
                                    </div>
                                </div>
                            @endforeach

                            @foreach($staffList as $staff)
                                @php($offsetIndex = $studentsList->take(30)->count() + $loop->index)
                                <div class="manual-person-item manual-staff-item hidden flex items-center justify-between p-2 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-700/60 text-xs">
                                    <div class="min-w-0 pr-2">
                                        <p class="font-medium text-slate-800 dark:text-slate-100 truncate">{{ $staff['name'] }}</p>
                                        <p class="text-[10px] text-slate-400">{{ $staff['role'] }}</p>
                                    </div>
                                    <input type="hidden" name="attendances[{{ $offsetIndex }}][id]" value="{{ $staff['id'] }}" disabled>
                                    <div class="flex items-center gap-1.5 shrink-0">
                                        <input type="time" name="attendances[{{ $offsetIndex }}][time]" value="{{ date('H:i') }}" class="manual-row-time rounded-lg border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs py-1 px-1.5 w-20" title="{{ __('Heure') }}" disabled>
                                        <select name="attendances[{{ $offsetIndex }}][status]" class="rounded-lg border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 text-xs py-1 px-2" disabled>
                                            <option value="present">{{ __('Present') }}</option>
                                            <option value="absent">{{ __('Absent') }}</option>
                                            <option value="late">{{ __('Late') }}</option>
                                            <option value="excused">{{ __('Excused') }}</option>
                                        </select>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="border-t border-slate-100 px-6 py-4 bg-slate-50/50 flex justify-end gap-2">
                    <button type="button" onclick="closeManualAttendanceModal()" class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                        {{ __('Cancel') }}
                    </button>
                    <button type="submit" class="rounded-xl bg-emerald-600 px-5 py-2 text-xs font-semibold text-white hover:bg-emerald-700 transition shadow-sm active:scale-95">
                        {{ __('Record Session') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endcan

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        // Modal functions
        async function showPersonDetails(type, id) {
            const modal = document.getElementById('details-modal');
            const nameEl = document.getElementById('modal-person-name');
            const subEl = document.getElementById('modal-person-sub');
            const avatarEl = document.getElementById('modal-person-avatar');
            const absEl = document.getElementById('modal-total-absences');
            const lateEl = document.getElementById('modal-total-late');
            const presEl = document.getElementById('modal-total-present');
            const listEl = document.getElementById('modal-records-container');

            nameEl.textContent = 'Chargement en cours...';
            subEl.textContent = '...';
            listEl.innerHTML = '<div class="py-8 text-center text-xs text-slate-400">Récupération des données...</div>';
            modal.classList.remove('hidden');
            modal.classList.add('flex');

            try {
                const response = await fetch(`/attendance/details/${type}/${id}`, {
                    headers: { 'Accept': 'application/json' }
                });
                if (!response.ok) throw new Error('Erreur');
                const data = await response.json();

                nameEl.textContent = data.person_name;
                subEl.textContent = data.person_sub;
                avatarEl.textContent = data.person_name.charAt(0).toUpperCase();
                absEl.textContent = data.total_absences;
                lateEl.textContent = data.total_late;
                presEl.textContent = data.total_present;

                if (!data.records.length) {
                    listEl.innerHTML = '<div class="py-8 text-center text-xs text-slate-400">{{ __('No absence recorded.') }}</div>';
                    return;
                }

                let html = '';
                data.records.forEach(rec => {
                    const badgeClass = rec.status === 'absent' ? 'bg-rose-100 text-rose-800' : (rec.status === 'late' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800');
                    html += `
                        <div class="p-3 rounded-xl border border-slate-100 bg-slate-50/60 hover:bg-slate-100/70 transition flex items-center justify-between gap-3 text-xs">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-slate-800">${rec.subject_name}</span>
                                    <span class="rounded px-2 py-0.5 text-[10px] font-bold ${badgeClass}">${rec.status_label}</span>
                                </div>
                                <p class="mt-1 text-[11px] text-slate-500">${rec.date} ${rec.time && rec.time !== '—' ? '· ' + rec.time : ''} · Classe: ${rec.class_name} · Méthode: <span class="font-medium">${rec.method}</span></p>
                            </div>
                            <span class="text-[10px] text-slate-400">${rec.recorded_by}</span>
                        </div>
                    `;
                });
                listEl.innerHTML = html;
            } catch (err) {
                listEl.innerHTML = '<div class="py-8 text-center text-xs text-rose-500">Impossible de charger les détails.</div>';
            }
        }

        function closeDetailsModal() {
            const modal = document.getElementById('details-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function openManualAttendanceModal() {
            const m = document.getElementById('manual-attendance-modal');
            if (m) {
                const now = new Date();
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                const currentTime = `${hours}:${minutes}`;
                const timeInput = document.getElementById('manual_attendance_time');
                if (timeInput) {
                    timeInput.value = currentTime;
                    syncManualAttendanceTime(currentTime);
                }
                toggleManualTarget();
                m.classList.remove('hidden');
                m.classList.add('flex');
            }
        }

        function closeManualAttendanceModal() {
            const m = document.getElementById('manual-attendance-modal');
            if (m) {
                m.classList.add('hidden');
                m.classList.remove('flex');
            }
        }

        function toggleManualTarget() {
            const sel = document.getElementById('manual_target_type');
            const stuFields = document.getElementById('manual-student-fields');
            const studentItems = document.querySelectorAll('.manual-student-item');
            const staffItems = document.querySelectorAll('.manual-staff-item');

            if (sel && sel.value === 'staff') {
                stuFields?.classList.add('hidden');
                studentItems.forEach(el => {
                    el.classList.add('hidden');
                    el.querySelectorAll('input, select').forEach(i => i.disabled = true);
                });
                staffItems.forEach(el => {
                    el.classList.remove('hidden');
                    el.querySelectorAll('input, select').forEach(i => i.disabled = false);
                });
            } else {
                stuFields?.classList.remove('hidden');
                studentItems.forEach(el => {
                    el.classList.remove('hidden');
                    el.querySelectorAll('input, select').forEach(i => i.disabled = false);
                });
                staffItems.forEach(el => {
                    el.classList.add('hidden');
                    el.querySelectorAll('input, select').forEach(i => i.disabled = true);
                });
            }
        }

        function syncManualAttendanceTime(val) {
            document.querySelectorAll('.manual-row-time').forEach(input => {
                input.value = val;
            });
        }

        // Charts Initialization
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof Chart === 'undefined') return;

            Chart.defaults.font.family = 'Figtree, sans-serif';
            Chart.defaults.color = '#64748b';

            // 1. Chart Absence by Class
            const ctxAbs = document.getElementById('classAbsenceChart')?.getContext('2d');
            if (ctxAbs) {
                const classLabels = @json($classStats->pluck('name'));
                const presenceRates = @json($classStats->pluck('presence_rate'));
                const absenceRates = @json($classStats->pluck('absence_rate'));

                new Chart(ctxAbs, {
                    type: 'bar',
                    data: {
                        labels: classLabels,
                        datasets: [
                            {
                                label: '{{ __('Student Presence Rate') }} (%)',
                                data: presenceRates,
                                backgroundColor: '#10b981',
                                borderRadius: 6,
                                barPercentage: 0.6,
                            },
                            {
                                label: '{{ __('Student Absence Rate') }} (%)',
                                data: absenceRates,
                                backgroundColor: '#f59e0b',
                                borderRadius: 6,
                                barPercentage: 0.6,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { font: { size: 11, weight: '600' } }
                            },
                            y: {
                                beginAtZero: true,
                                max: 100,
                                ticks: {
                                    callback: val => val + '%',
                                    font: { size: 10 }
                                },
                                grid: { color: '#f1f5f9' }
                            }
                        },
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: { font: { size: 11, weight: '500' }, boxWidth: 12 }
                            }
                        }
                    }
                });
            }

            // 2. Financial Forecast Chart
            const financialData = @json($financialMonths);
            const finCtx = document.getElementById('financialForecastChart')?.getContext('2d');
            if (finCtx && financialData.length > 0) {
                const labels = financialData.map(item => item.label);
                const revenues = financialData.map(item => item.revenue);
                const expenses = financialData.map(item => item.expense);
                const nets = financialData.map(item => item.net);

                new Chart(finCtx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: @json(__('Revenues')) + ' (Ar)',
                                data: revenues,
                                backgroundColor: 'rgba(16, 185, 129, 0.85)',
                                borderRadius: 5,
                                barPercentage: 0.65,
                                categoryPercentage: 0.6
                            },
                            {
                                label: @json(__('Expenses')) + ' (Ar)',
                                data: expenses,
                                backgroundColor: 'rgba(244, 63, 94, 0.85)',
                                borderRadius: 5,
                                barPercentage: 0.65,
                                categoryPercentage: 0.6
                            },
                            {
                                type: 'line',
                                label: @json(__('Net Balance')) + ' (Ar)',
                                data: nets,
                                borderColor: '#0ea5e9',
                                borderWidth: 2.5,
                                pointBackgroundColor: '#0284c7',
                                pointRadius: 3.5,
                                tension: 0.3,
                                fill: false
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                padding: 10,
                                cornerRadius: 8,
                                callbacks: {
                                    label: function(ctx) {
                                        let val = Number(ctx.raw || 0).toLocaleString('fr-FR');
                                        return `${ctx.dataset.label}: ${val} Ar`;
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { font: { size: 10 } }
                            },
                            y: {
                                border: { dash: [4, 4] },
                                grid: { color: '#f1f5f9' },
                                ticks: {
                                    font: { size: 10 },
                                    callback: function(value) {
                                        return (value / 1000).toLocaleString() + 'k Ar';
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // 3. Class Distribution Donut Chart
            const classData = @json($classDistribution);
            const donutCtx = document.getElementById('classDistributionDonutChart')?.getContext('2d');
            if (donutCtx && classData.length > 0) {
                const classNames = classData.map(c => c.name);
                const classCounts = classData.map(c => c.count);
                const palette = ['#10b981', '#0d9488', '#0ea5e9', '#6366f1', '#f59e0b', '#ec4899', '#8b5cf6', '#14b8a6'];

                new Chart(donutCtx, {
                    type: 'doughnut',
                    data: {
                        labels: classNames,
                        datasets: [{
                            data: classCounts,
                            backgroundColor: palette.slice(0, classNames.length),
                            borderWidth: 2,
                            borderColor: '#ffffff',
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '72%',
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: {
                                    boxWidth: 8,
                                    usePointStyle: true,
                                    padding: 10,
                                    font: { size: 10 }
                                }
                            }
                        }
                    }
                });
            }

            // 4. Growth Projection Line Chart
            const projectionData = @json($yearlyProjections);
            const growthCtx = document.getElementById('growthProjectionChart')?.getContext('2d');
            if (growthCtx && projectionData.length > 0) {
                const years = projectionData.map(p => p.year);
                const studentProj = projectionData.map(p => p.students);
                const capacityProj = projectionData.map(p => p.capacity);

                new Chart(growthCtx, {
                    type: 'line',
                    data: {
                        labels: years,
                        datasets: [
                            {
                                label: @json(__('Enrolled Students')),
                                data: studentProj,
                                borderColor: '#10b981',
                                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                borderWidth: 2.5,
                                fill: true,
                                tension: 0.3,
                                pointBackgroundColor: '#10b981',
                                pointRadius: 4
                            },
                            {
                                label: @json(__('School Capacity')),
                                data: capacityProj,
                                borderColor: '#94a3b8',
                                borderWidth: 2,
                                borderDash: [4, 4],
                                fill: false,
                                tension: 0.3,
                                pointBackgroundColor: '#94a3b8',
                                pointRadius: 3
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    boxWidth: 10,
                                    usePointStyle: true,
                                    padding: 12,
                                    font: { size: 10 }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { font: { size: 10 } }
                            },
                            y: {
                                border: { dash: [4, 4] },
                                grid: { color: '#f1f5f9' },
                                ticks: { font: { size: 10 } }
                            }
                        }
                    }
                });
            }
        });
    </script>
</x-app-layout>
