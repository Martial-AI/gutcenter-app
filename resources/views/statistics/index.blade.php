<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold tracking-tight text-slate-800">{{ __('Statistics') }}</h2>
                <p class="mt-0.5 text-xs text-slate-500">{{ __('Analytics & Financial Projections, student distributions and school capacity.') }}</p>
            </div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-600 bg-white border border-slate-200/90 rounded-xl px-3.5 py-2 shadow-sm">
                <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>{{ __('School Capacity') }}: <strong class="text-slate-800">{{ $studentCount }} / {{ $totalCapacity }} ({{ $capacityOccupancyRate }}%)</strong></span>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Summary Metric Cards -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Card 1: Total Students -->
                <div class="rounded-2xl bg-gradient-to-br from-emerald-600 to-teal-700 p-5 text-white shadow-sm transition hover:-translate-y-0.5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-emerald-100/90">{{ __('Total Enrolled Students') }}</span>
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-white/15">
                            <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        </div>
                    </div>
                    <p class="mt-3 text-3xl font-black tracking-tight">{{ $studentCount }}</p>
                    <p class="mt-1 text-xs text-emerald-100/80">{{ __('Active in current academic year') }}</p>
                </div>

                <!-- Card 2: Classes -->
                <div class="rounded-2xl bg-white p-5 border border-slate-200/70 shadow-sm transition hover:-translate-y-0.5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-slate-500">{{ __('Classes') }}</span>
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        </div>
                    </div>
                    <p class="mt-3 text-3xl font-black tracking-tight text-slate-800">{{ $classes->count() }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ __('Total registered class groups') }}</p>
                </div>

                <!-- Card 3: Teachers & Staff -->
                <div class="rounded-2xl bg-white p-5 border border-slate-200/70 shadow-sm transition hover:-translate-y-0.5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-slate-500">{{ __('Teachers & Staff') }}</span>
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-purple-50 text-purple-600">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </div>
                    </div>
                    <p class="mt-3 text-3xl font-black tracking-tight text-slate-800">{{ $teacherCount + $staffCount }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ $teacherCount }} {{ __('Teachers') }} · {{ $staffCount }} {{ __('Staff') }}</p>
                </div>

                <!-- Card 4: Global Occupancy -->
                <div class="rounded-2xl bg-white p-5 border border-slate-200/70 shadow-sm transition hover:-translate-y-0.5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-slate-500">{{ __('Occupancy Rate') }}</span>
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-sky-50 text-sky-600">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        </div>
                    </div>
                    <p class="mt-3 text-3xl font-black tracking-tight text-slate-800">{{ $capacityOccupancyRate }}%</p>
                    <p class="mt-1 text-xs text-slate-500">{{ $totalCapacity }} {{ __('Max school capacity') }}</p>
                </div>
            </div>

            <!-- SECTION 1: ANALYTICS & PROJECTIONS FINANCIÈRES -->
            <div class="space-y-6">
                <!-- 1. Diagramme en bâtons - Budgets & Projections Financières -->
                <div class="rounded-2xl bg-white p-6 border border-slate-200/70 shadow-sm transition hover:shadow-md">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4">
                        <div>
                            <h3 class="text-base font-bold text-slate-800">{{ __('Budget & Financial Forecasts (12 Months)') }}</h3>
                            <p class="text-xs text-slate-500">{{ __('Historical revenue & expenses vs projected future monthly net balance.') }}</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-3 text-xs">
                            <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm bg-emerald-500"></span> {{ __('Revenues') }}</span>
                            <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm bg-rose-500"></span> {{ __('Expenses') }}</span>
                            <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-sky-500"></span> {{ __('Net Balance') }}</span>
                        </div>
                    </div>
                    
                    <div class="h-72 w-full">
                        <canvas id="financialForecastChart"></canvas>
                    </div>
                </div>

                <!-- 2. Dual Grid: Donut Chart & Line Growth Chart -->
                <div class="grid gap-6 lg:grid-cols-2">
                    <!-- Left: Donut Chart - Répartition des Élèves par Classe -->
                    <div class="rounded-2xl bg-white p-6 border border-slate-200/70 shadow-sm transition hover:shadow-md flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h4 class="text-sm font-bold text-slate-800">{{ __('Student Distribution by Class') }}</h4>
                                    <p class="text-xs text-slate-500">{{ __('Breakdown of active students across active classes.') }}</p>
                                </div>
                                <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-600">{{ $classes->count() }} {{ __('Classes') }}</span>
                            </div>

                            @if($classDistribution->isNotEmpty())
                                <div class="relative h-64 w-full flex items-center justify-center my-1">
                                    <canvas id="classDistributionDonutChart"></canvas>
                                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none text-center">
                                        <span class="text-2xl font-black text-slate-800">{{ $studentCount }}</span>
                                        <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">{{ __('Students') }}</span>
                                    </div>
                                </div>
                            @else
                                <div class="flex h-64 items-center justify-center text-xs text-slate-400">
                                    {{ __('No class distribution data available yet.') }}
                                </div>
                            @endif
                        </div>
                        
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                            <span>{{ __('Total Registered Classes') }}: <strong>{{ $classes->count() }}</strong></span>
                            <span>{{ __('Total Occupancy') }}: <strong>{{ $capacityOccupancyRate }}%</strong></span>
                        </div>
                    </div>

                    <!-- Right: Line Chart - Projections d'Évolution des Effectifs -->
                    <div class="rounded-2xl bg-white p-6 border border-slate-200/70 shadow-sm transition hover:shadow-md flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h4 class="text-sm font-bold text-slate-800">{{ __('Multi-Year Growth & Capacity Forecast') }}</h4>
                                    <p class="text-xs text-slate-500">{{ __('Historical student numbers & 3-year capacity projections.') }}</p>
                                </div>
                                <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 border border-emerald-200/60">{{ __('Target Growth') }}</span>
                            </div>

                            <div class="h-64 w-full my-1">
                                <canvas id="growthProjectionChart"></canvas>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                            <span>{{ __('School Capacity') }}: <strong>{{ $totalCapacity }}</strong></span>
                            <span class="text-emerald-700 font-semibold">{{ __('Sustainable Expansion') }}</span>
                        </div>
                    </div>
                </div>

                <!-- 3. Financial Forecast Table -->
                <div class="overflow-hidden rounded-2xl bg-white border border-slate-200/70 shadow-sm">
                    <div class="border-b border-slate-100 px-6 py-4 bg-slate-50/70">
                        <h4 class="font-bold text-slate-900 text-sm">{{ __('Monthly Financial Breakdown') }}</h4>
                        <p class="text-xs text-slate-500">{{ __('Detailed history and upcoming projection balances.') }}</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50/50 border-b border-slate-100 text-slate-500 font-semibold uppercase tracking-wider text-[11px]">
                                <tr>
                                    <th class="px-6 py-3">{{ __('Month') }}</th>
                                    <th class="px-6 py-3">{{ __('Type') }}</th>
                                    <th class="px-6 py-3 text-right">{{ __('Revenues') }}</th>
                                    <th class="px-6 py-3 text-right">{{ __('Expenses') }}</th>
                                    <th class="px-6 py-3 text-right">{{ __('Net Balance') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($financialMonths as $month)
                                    <tr class="hover:bg-slate-50/80 transition {{ $month['is_projection'] ? 'bg-sky-50/30' : '' }}">
                                        <td class="px-6 py-3.5 font-medium text-slate-800">
                                            {{ $month['label'] }}
                                        </td>
                                        <td class="px-6 py-3.5">
                                            @if($month['is_projection'])
                                                <span class="inline-flex items-center gap-1 rounded-full bg-sky-100 px-2.5 py-0.5 text-[10px] font-bold text-sky-700 border border-sky-200">
                                                    {{ __('Projection') }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-[10px] font-bold text-emerald-700 border border-emerald-200">
                                                    {{ __('Actual') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-3.5 text-right font-semibold text-emerald-700">
                                            {{ number_format((float) $month['revenue'], 0, ',', ' ') }} Ar
                                        </td>
                                        <td class="px-6 py-3.5 text-right font-semibold text-rose-600">
                                            {{ number_format((float) $month['expense'], 0, ',', ' ') }} Ar
                                        </td>
                                        <td class="px-6 py-3.5 text-right font-bold {{ $month['net'] >= 0 ? 'text-sky-700' : 'text-amber-600' }}">
                                            {{ number_format((float) $month['net'], 0, ',', ' ') }} Ar
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-8 text-center text-slate-400">
                                            {{ __('No financial data recorded yet.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        </div>
    </div>

    <!-- Chart.js Engine CDN Script -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // 1. Financial Forecast Chart
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
                                borderRadius: 6,
                                barPercentage: 0.65,
                                categoryPercentage: 0.6
                            },
                            {
                                label: @json(__('Expenses')) + ' (Ar)',
                                data: expenses,
                                backgroundColor: 'rgba(244, 63, 94, 0.85)',
                                borderRadius: 6,
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
                                pointRadius: 4,
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

            // 2. Class Distribution Donut Chart
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
                            hoverOffset: 6
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
                                    padding: 12,
                                    font: { size: 11 }
                                }
                            }
                        }
                    }
                });
            }

            // 3. Growth Projection Line Chart
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
                                backgroundColor: 'rgba(16, 185, 129, 0.12)',
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
                                    font: { size: 11 }
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
