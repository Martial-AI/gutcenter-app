<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-bold tracking-tight text-slate-800">{{ __('Dashboard') }}</h2>
                <p class="mt-0.5 text-xs text-slate-500">{{ __('Overview of your school management key metrics & financial projections.') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 border border-emerald-200/80">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    {{ __('Live Data & Forecasts') }}
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Compact Top Stats Grid (6 Cards) -->
            <div class="grid gap-3.5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
                <!-- Enrolled Students Card -->
                <div class="relative overflow-hidden rounded-xl bg-gradient-to-br from-emerald-600 to-teal-700 p-4 text-white shadow-md shadow-emerald-700/10 transition-all duration-200 hover:-translate-y-0.5 active:scale-[0.98]">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-emerald-100/90">{{ __('Enrolled students') }}</span>
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-white/15 backdrop-blur-md">
                            <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0112 20.055a11.952 11.952 0 01-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                        </div>
                    </div>
                    <p class="mt-2 text-2xl font-extrabold tracking-tight">{{ $studentCount }}</p>
                    <div class="mt-1 text-[11px] text-emerald-100/80">
                        <span>{{ $capacityOccupancyRate }}% {{ __('of capacity') }}</span>
                    </div>
                </div>

                <!-- Teachers Card -->
                <div class="rounded-xl bg-white p-4 border border-slate-200/60 shadow-sm transition-all duration-200 hover:-translate-y-0.5 active:scale-[0.98]">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-slate-500">{{ __('Teachers') }}</span>
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        </div>
                    </div>
                    <p class="mt-2 text-2xl font-extrabold tracking-tight text-slate-800">{{ $teacherCount }}</p>
                    <div class="mt-1 text-[11px] text-slate-400">
                        <span>{{ __('Active teaching staff') }}</span>
                    </div>
                </div>

                <!-- Administrative Staff Card -->
                @can('roles.manage')
                <div class="rounded-xl bg-white p-4 border border-slate-200/60 shadow-sm transition-all duration-200 hover:-translate-y-0.5 active:scale-[0.98]">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-slate-500">{{ __('Administrative staff') }}</span>
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-sky-50 text-sky-600">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </div>
                    </div>
                    <p class="mt-2 text-2xl font-extrabold tracking-tight text-slate-800">{{ $staffCount }}</p>
                    <div class="mt-1 text-[11px] text-slate-400">
                        <span>{{ __('Management & Secretarial team') }}</span>
                    </div>
                </div>
                @endcan

                <!-- Overdue School Fees Card -->
                @can('payments.view')
                <div class="rounded-xl bg-rose-50/70 p-4 border border-rose-200/60 shadow-sm transition-all duration-200 hover:-translate-y-0.5 active:scale-[0.98]">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-rose-700">{{ __('Overdue school fees') }}</span>
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-rose-100 text-rose-700">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                    <p class="mt-2 text-xl font-extrabold tracking-tight text-rose-800">{{ number_format($overdueInvoices, 0, ',', ' ') }} Ar</p>
                    <div class="mt-1 text-[11px] text-rose-600 font-medium">
                        <span>{{ __('Recovery rate') }}: {{ $recoveryRate }}%</span>
                    </div>
                </div>

                <!-- Amount Collected Card -->
                <div class="rounded-xl bg-emerald-50/70 p-4 border border-emerald-200/60 shadow-sm transition-all duration-200 hover:-translate-y-0.5 active:scale-[0.98]">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-emerald-800">{{ __('Amount collected') }}</span>
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-100 text-emerald-800">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                    <p class="mt-2 text-xl font-extrabold tracking-tight text-emerald-900">{{ number_format($collectedAmount, 0, ',', ' ') }} Ar</p>
                    <div class="mt-1 text-[11px] text-emerald-700 font-medium">
                        <span>{{ __('Validated payments') }}</span>
                    </div>
                </div>
                @endcan

                <!-- Expenses Card -->
                @can('expenses.view')
                <div class="rounded-xl bg-amber-50/70 p-4 border border-amber-200/60 shadow-sm transition-all duration-200 hover:-translate-y-0.5 active:scale-[0.98]">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-amber-800">{{ __('Expenses') }}</span>
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-100 text-amber-800">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </div>
                    </div>
                    <p class="mt-2 text-xl font-extrabold tracking-tight text-amber-900">{{ number_format($totalExpenses, 0, ',', ' ') }} Ar</p>
                    <div class="mt-1 text-[11px] text-amber-700 font-medium">
                        <span>{{ __('Operating expenses & salaries') }}</span>
                    </div>
                </div>
                @endcan
            </div>

            <!-- Compact Performance KPI Gauges -->
            <div class="grid gap-3.5 sm:grid-cols-3">
                <div class="rounded-xl bg-white p-4 border border-slate-200/60 shadow-sm">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ __('Capacity Occupancy') }}</span>
                        <span class="text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full">{{ $capacityOccupancyRate }}%</span>
                    </div>
                    <div class="h-2 w-full rounded-full bg-slate-100 overflow-hidden">
                        <div class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-teal-600 transition-all duration-500" style="width: {{ $capacityOccupancyRate }}%"></div>
                    </div>
                    <p class="mt-1.5 text-[11px] text-slate-400">{{ $studentCount }} {{ __('students out of') }} {{ $totalCapacity }} {{ __('capacity places') }}</p>
                </div>

                <div class="rounded-xl bg-white p-4 border border-slate-200/60 shadow-sm">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ __('Fee Collection Rate') }}</span>
                        <span class="text-[11px] font-bold text-teal-700 bg-teal-50 px-2 py-0.5 rounded-full">{{ $recoveryRate }}%</span>
                    </div>
                    <div class="h-2 w-full rounded-full bg-slate-100 overflow-hidden">
                        <div class="h-full rounded-full bg-gradient-to-r from-teal-500 to-sky-600 transition-all duration-500" style="width: {{ $recoveryRate }}%"></div>
                    </div>
                    <p class="mt-1.5 text-[11px] text-slate-400">{{ __('Healthy payment collection ratio') }}</p>
                </div>

                <div class="rounded-xl bg-white p-4 border border-slate-200/60 shadow-sm">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ __('Global Attendance') }}</span>
                        <span class="text-[11px] font-bold text-sky-700 bg-sky-50 px-2 py-0.5 rounded-full">{{ $attendanceRate }}%</span>
                    </div>
                    <div class="h-2 w-full rounded-full bg-slate-100 overflow-hidden">
                        <div class="h-full rounded-full bg-gradient-to-r from-sky-500 to-indigo-600 transition-all duration-500" style="width: {{ $attendanceRate }}%"></div>
                    </div>
                    <p class="mt-1.5 text-[11px] text-slate-400">{{ __('Presence & punctuality percentage') }}</p>
                </div>
            </div>

            <!-- Compact Quick Actions Banner -->
            @can('students.create')
            <div class="rounded-xl bg-white p-4 border border-slate-200/60 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">{{ __('Quick actions') }}</h3>
                        <p class="text-xs text-slate-500">{{ __('Shortcuts to perform frequent tasks quickly.') }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        @can('roles.manage')
                        <button type="button" title="{{ __('Trash') }}" onclick="openTrashModal()" class="relative inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-800 text-white shadow-sm transition hover:bg-slate-900 active:scale-95">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                        @endcan
                        <a class="inline-flex items-center gap-1.5 rounded-lg bg-gradient-to-r from-emerald-600 to-teal-600 px-3.5 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:from-emerald-700 hover:to-teal-700 active:scale-95" href="{{ route('students.create') }}">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                            <span>{{ __('Enroll a student') }}</span>
                        </a>

                        @can('login_history.view')
                        <a class="inline-flex items-center gap-1.5 rounded-lg bg-slate-800 px-3.5 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-slate-900 active:scale-95" href="{{ route('admin.history.index') }}">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>{{ __('History') }}</span>
                        </a>
                        @endcan
                    </div>
                </div>
            </div>
            @endcan

            <!-- SECTION DES GRAPHIQUES COMPACTS ET TRADUITS -->
            @can('statistics.view')
            <div class="space-y-4 pt-1">
                <div class="flex items-center justify-between border-b border-slate-200/80 pb-2">
                    <div>
                        <h3 class="text-base font-bold text-slate-800">{{ __('Analytics & Financial Projections') }}</h3>
                        <p class="text-xs text-slate-500">{{ __('Bar charts & circular metrics of future budgets and student distribution.') }}</p>
                    </div>
                </div>

                <!-- 1. Diagramme en bâtons - Budgets & Projections Financières -->
                <div class="rounded-xl bg-white p-5 border border-slate-200/60 shadow-sm transition-all hover:shadow-md">
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
                    
                    <div class="h-56 w-full">
                        <canvas id="financialForecastChart"></canvas>
                    </div>
                </div>

                <!-- 2. Dual Grid: Donut Chart & Line Growth Chart -->
                <div class="grid gap-4 lg:grid-cols-2">
                    <!-- Left: Donut Chart - Répartition des Élèves par Classe -->
                    <div class="rounded-xl bg-white p-5 border border-slate-200/60 shadow-sm transition-all hover:shadow-md flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <h4 class="text-sm font-bold text-slate-800">{{ __('Student Distribution by Class') }}</h4>
                                    <p class="text-xs text-slate-500">{{ __('Breakdown of active students across active classes.') }}</p>
                                </div>
                                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600">{{ $classesCount ?? $classDistribution->count() }} {{ __('Classes') }}</span>
                            </div>

                            @if($classDistribution->isNotEmpty())
                                <div class="relative h-52 w-full flex items-center justify-center my-1">
                                    <canvas id="classDistributionDonutChart"></canvas>
                                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none text-center">
                                        <span class="text-xl font-extrabold text-slate-800">{{ $studentCount }}</span>
                                        <span class="text-[10px] font-medium text-slate-400 uppercase tracking-wider">{{ __('Students') }}</span>
                                    </div>
                                </div>
                            @else
                                <div class="flex h-52 items-center justify-center text-xs text-slate-400">
                                    {{ __('No class distribution data available yet.') }}
                                </div>
                            @endif
                        </div>
                        
                        <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                            <span>{{ __('Total Registered Classes') }}: <strong>{{ $classesCount ?? $classDistribution->count() }}</strong></span>
                            <span>{{ __('Total Occupancy') }}: <strong>{{ $capacityOccupancyRate }}%</strong></span>
                        </div>
                    </div>

                    <!-- Right: Line Chart - Projections d'Évolution des Effectifs -->
                    <div class="rounded-xl bg-white p-5 border border-slate-200/60 shadow-sm transition-all hover:shadow-md flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <h4 class="text-sm font-bold text-slate-800">{{ __('Multi-Year Growth & Capacity Forecast') }}</h4>
                                    <p class="text-xs text-slate-500">{{ __('Historical student numbers & 3-year capacity projections.') }}</p>
                                </div>
                                <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700 border border-emerald-200/60">{{ __('Target Growth') }}</span>
                            </div>

                            <div class="h-52 w-full my-1">
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
            @endcan
        </div>
    </div>

    <!-- Modals (Trash, etc) -->
    @can('roles.manage')
    @php($trashItems = \App\Models\DeletedItem::with('deletedBy')->latest('deleted_at')->take(30)->get())
    <div id="trash-modal" class="fixed inset-0 z-[90] hidden items-center justify-center bg-black/40 p-4 backdrop-blur-md">
        <div class="flex max-h-[85vh] w-full max-w-4xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <div>
                    <h2 class="font-bold text-slate-900">{{ __('Trash') }}</h2>
                    <p class="text-xs text-slate-500">{{ __('Deleted items can be restored here.') }}</p>
                </div>
                <button type="button" onclick="closeTrashModal()" class="rounded-lg px-3 py-2 text-slate-500 hover:bg-slate-100 transition active:scale-95">{{ __('Close') }}</button>
            </div>
            <div class="overflow-y-auto p-6">
                <div class="space-y-3">
                    @forelse($trashItems as $item)
                        <div class="flex flex-col gap-3 rounded-xl border border-slate-100 bg-slate-50/50 p-4 sm:flex-row sm:items-center sm:justify-between transition hover:bg-slate-100/60">
                            <div>
                                <p class="font-semibold text-slate-800">{{ $item->localizedLabel() }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $item->deletedBy?->localizedFunctionLabel() ?? __('System') }} · {{ $item->deleted_at->format('d/m/Y H:i') }}</p>
                            </div>
                            <div class="flex gap-2">
                                <form method="POST" action="{{ route('admin.trash.restore', $item) }}">
                                    @csrf
                                    <button class="rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:from-emerald-700 hover:to-teal-700 active:scale-95">{{ __('Restore') }}</button>
                                </form>
                                <a href="{{ route('admin.trash.index') }}" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-2 text-xs font-semibold text-rose-700 transition hover:bg-rose-100 active:scale-95">{{ __('Delete permanently') }}</a>
                            </div>
                        </div>
                    @empty
                        <p class="py-10 text-center text-sm text-slate-500">{{ __('Trash is empty.') }}</p>
                    @endforelse
                </div>
            </div>
            <div class="border-t border-slate-100 px-6 py-4 text-right">
                <a href="{{ route('admin.trash.index') }}" class="mr-3 text-sm font-semibold text-emerald-700 hover:underline">{{ __('View all') }}</a>
                <button type="button" onclick="closeTrashModal()" class="rounded-xl bg-slate-800 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-900 active:scale-95">{{ __('Close') }}</button>
            </div>
        </div>
    </div>
    <script>
        function openTrashModal(){document.getElementById('trash-modal').classList.remove('hidden');document.getElementById('trash-modal').classList.add('flex')}
        function closeTrashModal(){document.getElementById('trash-modal').classList.add('hidden');document.getElementById('trash-modal').classList.remove('flex')}
    </script>
    @endcan

    @can('roles.manage')<script>document.addEventListener('DOMContentLoaded',()=>document.querySelector('#trash-modal .border-t button[onclick="closeTrashModal()"]')?.remove());</script>@endcan
    @can('roles.manage')@php($trashCount = \App\Models\DeletedItem::count())<script>document.addEventListener('DOMContentLoaded',()=>{const button=document.querySelector('button[onclick="openTrashModal()"]');if(!button)return;button.classList.add('relative');const badge=document.createElement('span');badge.id='trash-count-badge';badge.textContent='{{ $trashCount > 99 ? '99+' : $trashCount }}';badge.style.cssText='position:absolute;top:-7px;right:-7px;min-width:20px;height:20px;padding:0 4px;border-radius:999px;background:#dc2626;color:#fff;font-size:11px;font-weight:700;display:{{ $trashCount ? 'flex' : 'none' }};align-items:center;justify-content:center;';button.appendChild(badge);window.updateTrashBadge=increment=>{const current=Number(button.dataset.trashCount||{{ $trashCount }});const next=current+Number(increment);button.dataset.trashCount=next;badge.textContent=next>99?'99+':next;badge.style.display=next?'flex':'none'}});</script>@endcan
    @can('roles.manage')<div id="trash-empty-modal" class="fixed inset-0 z-[110] hidden items-center justify-center bg-black/40 p-4 backdrop-blur-md"><form method="POST" action="{{ route('admin.trash.destroy-all') }}" class="w-full max-w-sm rounded-2xl bg-white p-6 text-center shadow-2xl">@csrf @method('DELETE')<h2 class="text-lg font-semibold text-slate-900">{{ __('Empty trash permanently') }}</h2><p class="mt-2 text-sm text-slate-600">{{ __('This action cannot be undone. Enter your administrator password.') }}</p><input type="password" name="admin_password" required autocomplete="current-password" class="mt-4 w-full rounded-xl border-slate-300 text-left"><div class="mt-6 flex justify-center gap-3"><button type="button" onclick="closeEmptyTrashModal()" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold transition active:scale-95">{{ __('Cancel') }}</button><button class="rounded-xl bg-gradient-to-r from-rose-600 to-red-600 px-4 py-2 text-sm font-semibold text-white transition active:scale-95">{{ __('Delete all permanently') }}</button></div></form></div><script>function openEmptyTrashModal(){const modal=document.getElementById('trash-empty-modal');modal.classList.remove('hidden');modal.classList.add('flex')}function closeEmptyTrashModal(){const modal=document.getElementById('trash-empty-modal');modal.classList.add('hidden');modal.classList.remove('flex')}document.addEventListener('DOMContentLoaded',()=>{document.querySelectorAll('#trash-modal a[href$="/admin/trash"]').forEach(link=>link.remove());const footer=document.querySelector('#trash-modal .border-t');if(footer){const button=document.createElement('button');button.type='button';button.textContent='{{ __('Delete all permanently') }}';button.className='rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white transition active:scale-95';button.onclick=openEmptyTrashModal;footer.prepend(button)}});</script>@endcan
    @can('roles.manage')<script>document.addEventListener('DOMContentLoaded',()=>{const modal=document.getElementById('trash-empty-modal');const open=()=>{modal.style.cssText='position:fixed;inset:0;z-index:10000;display:flex;align-items:center;justify-content:center;';modal.classList.remove('hidden');modal.classList.add('flex')};window.openEmptyTrashModal=open;const button=[...document.querySelectorAll('#trash-modal .border-t button')].find(item=>item.textContent.trim()==='{{ __('Delete all permanently') }}');if(button)button.onclick=open;});</script>@endcan
    @can('roles.manage')<script>window.closeEmptyTrashModal=()=>{const modal=document.getElementById('trash-empty-modal');modal.style.display='none';modal.classList.add('hidden');modal.classList.remove('flex')};</script>@endcan

    <!-- Chart.js Engine CDN Script -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <!-- Initialisation des Graphiques avec support bilingue -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof Chart === 'undefined') return;

            // Global Chart Defaults
            Chart.defaults.font.family = 'Figtree, sans-serif';
            Chart.defaults.color = '#64748b';

            // 1. Diagramme en Bâtons - Budget & Projections Financières
            const financialData = @json($financialMonths);
            const finCtx = document.getElementById('financialForecastChart')?.getContext('2d');
            if (finCtx) {
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

            // 2. Diagramme en Anneau (Donut Chart) - Répartition des Élèves par Classe
            const classData = @json($classDistribution);
            const donutCtx = document.getElementById('classDistributionDonutChart')?.getContext('2d');
            if (donutCtx && classData.length > 0) {
                const classNames = classData.map(c => c.name);
                const classCounts = classData.map(c => c.count);

                const palette = [
                    '#10b981', '#0d9488', '#0ea5e9', '#6366f1', 
                    '#f59e0b', '#ec4899', '#8b5cf6', '#14b8a6'
                ];

                new Chart(donutCtx, {
                    type: 'doughnut',
                    data: {
                        labels: classNames,
                        datasets: [{
                            data: classCounts,
                            backgroundColor: palette.slice(0, classNames.length),
                            borderWidth: 2,
                            borderColor: '#ffffff',
                            hoverOffset: 5
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    boxWidth: 10,
                                    usePointStyle: true,
                                    pointStyle: 'circle',
                                    padding: 12,
                                    font: { size: 10 }
                                }
                            },
                            tooltip: {
                                padding: 8,
                                cornerRadius: 8,
                                callbacks: {
                                    label: function(ctx) {
                                        const count = ctx.raw || 0;
                                        return ` ${ctx.label}: ${count} ` + @json(__('Students'));
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // 3. Diagramme d'Évolution (Line & Area Chart) - Projections Futures
            const growthData = @json($yearlyProjections);
            const growthCtx = document.getElementById('growthProjectionChart')?.getContext('2d');
            if (growthCtx) {
                const years = growthData.map(g => g.year);
                const studentProj = growthData.map(g => g.students);
                const capacityProj = growthData.map(g => g.capacity);

                new Chart(growthCtx, {
                    type: 'line',
                    data: {
                        labels: years,
                        datasets: [
                            {
                                label: @json(__('Enrolled Students')),
                                data: studentProj,
                                borderColor: '#059669',
                                backgroundColor: 'rgba(16, 185, 129, 0.10)',
                                borderWidth: 2.5,
                                fill: true,
                                tension: 0.3,
                                pointBackgroundColor: '#059669',
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
                            },
                            tooltip: {
                                padding: 8,
                                cornerRadius: 8
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
