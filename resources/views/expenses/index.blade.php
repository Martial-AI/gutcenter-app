<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-xl font-semibold text-gray-800">{{ __('Expenses') }}</h2>
            @can('expenses.create')
                <button type="button" onclick="openExpenseModal()" class="rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-800">
                    + {{ __('Add expense') }}
                </button>
            @endcan
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ $errors->first() }}</div>
            @endif

            <div class="grid gap-5 md:grid-cols-3">
                <div class="max-w-md rounded-2xl bg-gradient-to-br from-emerald-600 to-red-600 p-6 text-white shadow-lg" style="background:linear-gradient(135deg,rgba(16,185,129,.72) 0%,rgba(220,38,38,.72) 100%)">
                    <p class="text-sm font-medium text-orange-100">{{ __('Total expenses') }}</p>
                    <p class="mt-2 inline-flex w-fit max-w-full rounded-xl px-4 py-2 text-3xl font-bold shadow-inner" style="background:rgba(127,29,29,.48)">{{ number_format((float) $totalExpenses, 0, ',', ' ') }} Ar</p>
                    <p class="mt-1 text-xs text-orange-100">{{ __('Manual expenses and salary payments') }}</p>
                </div>
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100 md:col-span-2">
                    <p class="text-sm font-semibold text-slate-700">{{ __('Expense ledger') }}</p>
                    <p class="mt-1 text-sm text-slate-500">{{ __('Salary payments are added automatically as expenses.') }}</p>
                </div>
            </div>

            <section class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-100">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-5 py-3">{{ __('Description') }}</th>
                                <th class="px-5 py-3">{{ __('Date') }}</th>
                                <th class="px-5 py-3">{{ __('Time') }}</th>
                                <th class="px-5 py-3">{{ __('Added by') }}</th>
                                <th class="px-5 py-3 text-right">{{ __('Amount') }}</th>
                                @if(auth()->user()?->can('expenses.delete') || auth()->user()?->hasRole('Admin'))
                                    <th class="px-5 py-3 text-right">{{ __('Actions') }}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($expenses as $expense)
                                @php($salary = $expense->employee_payment_id !== null)
                                <tr class="hover:bg-slate-50/70">
                                    <td class="px-5 py-4">
                                        <div class="font-medium text-slate-800">{{ $expense->reason }}</div>
                                        <div class="mt-1 text-xs text-slate-500">
                                            <span class="rounded-full {{ $salary ? 'bg-violet-100 text-violet-700' : 'bg-slate-100 text-slate-600' }} px-2 py-0.5">{{ $salary ? __('Salary payment') : $expense->category }}</span>
                                            <span class="ml-2">{{ $expense->reference }}</span>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 text-slate-600">{{ ($expense->spent_at ?? $expense->spent_on)?->format('d/m/Y') ?? '—' }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-slate-600">{{ $expense->spent_at?->format('H:i') ?? $expense->created_at?->format('H:i') ?? '—' }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-slate-600">{{ $expense->recordedBy?->localizedFunctionLabel() ?? __('System') }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-right"><p class="font-bold text-red-700">{{ number_format((float) $expense->amount, 0, ',', ' ') }} Ar</p></td>
                                    @if(auth()->user()?->can('expenses.delete') || auth()->user()?->hasRole('Admin'))
                                        <td class="whitespace-nowrap px-5 py-4 text-right">
                                            <button type="button"
                                                    onclick="openDeleteExpenseModal('{{ route('expenses.destroy', $expense) }}', '{{ addslashes($expense->reason) }}', '{{ number_format((float) $expense->amount, 0, ',', ' ') }} Ar')"
                                                    class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-red-50/80 px-2.5 py-1.5 text-xs font-semibold text-red-700 shadow-sm transition hover:bg-red-100 hover:border-red-300 focus:outline-none focus:ring-2 focus:ring-red-500/20"
                                                    title="{{ __('Delete expense') }}">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                </svg>
                                                <span>{{ __('Delete') }}</span>
                                            </button>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr><td colspan="{{ (auth()->user()?->can('expenses.delete') || auth()->user()?->hasRole('Admin')) ? 6 : 5 }}" class="px-5 py-12 text-center text-slate-500">{{ __('No expense recorded.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($expenses->hasPages())
                    <div class="border-t border-slate-100 px-5 py-4">{{ $expenses->links() }}</div>
                @endif
            </section>
        </div>
    </div>

    @can('expenses.create')
        <div id="expense-modal" class="fixed inset-0 z-[10000] hidden items-center justify-center bg-black/70 p-4" style="background-color:rgba(0,0,0,.70)">
            <form method="POST" action="{{ route('expenses.store') }}" class="min-h-[390px] max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-xl bg-white p-6 shadow-xl">
                @csrf
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-slate-900">{{ __('Add expense') }}</h3>
                    <button type="button" onclick="closeExpenseModal()" class="rounded-lg px-3 py-1 text-xl text-slate-500 hover:bg-slate-100" aria-label="{{ __('Close') }}">×</button>
                </div>
                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    <label class="sm:col-span-2"><span class="mb-1 block text-sm font-medium text-slate-700">{{ __('Category') }}</span><input name="category" required maxlength="100" class="w-full rounded-lg border-slate-300" placeholder="{{ __('For example: supplies') }}"></label>
                    <label class="sm:col-span-2"><span class="mb-1 block text-sm font-medium text-slate-700">{{ __('Description') }}</span><textarea name="reason" required maxlength="1000" rows="3" class="w-full rounded-lg border-slate-300"></textarea></label>
                    <label><span class="mb-1 block text-sm font-medium text-slate-700">{{ __('Amount') }}</span><input type="number" name="amount" required min="0.01" step="0.01" placeholder="ex: 50 000 Ar" class="w-full rounded-lg border-slate-300"></label>

                    <label><span class="mb-1 block text-sm font-medium text-slate-700">{{ __('Date and time') }}</span><input type="datetime-local" name="spent_at" required value="{{ now()->format('Y-m-d\TH:i') }}" class="w-full rounded-lg border-slate-300"></label>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" onclick="closeExpenseModal()" class="rounded-lg border border-slate-300 px-4 py-2 text-sm">{{ __('Cancel') }}</button>
                    <button class="rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">{{ __('Save') }}</button>
                </div>
            </form>
        </div>
        <script>
            function openExpenseModal(){const m=document.getElementById('expense-modal');m.classList.remove('hidden');m.classList.add('flex')}
            function closeExpenseModal(){const m=document.getElementById('expense-modal');m.classList.add('hidden');m.classList.remove('flex')}
        </script>
    @endcan

    @if(auth()->user()?->can('expenses.delete') || auth()->user()?->hasRole('Admin'))
        <div id="delete-expense-modal" class="fixed inset-0 z-[10000] hidden items-center justify-center bg-black/60 p-4 backdrop-blur-sm" style="background-color:rgba(0,0,0,.60)">
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl transition-all">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-full bg-red-100 text-red-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">{{ __('Delete expense') }}</h3>
                        <p class="text-xs text-slate-500">{{ __('Confirmation required') }}</p>
                    </div>
                </div>

                <div class="mt-4 rounded-xl border border-slate-100 bg-slate-50/80 p-3.5 text-xs text-slate-700 space-y-1.5">
                    <div class="flex justify-between items-start gap-2">
                        <span class="text-slate-500 font-medium">{{ __('Description') }}:</span>
                        <span id="delete-expense-desc" class="font-semibold text-slate-900 text-right"></span>
                    </div>
                    <div class="flex justify-between items-center gap-2 pt-1 border-t border-slate-200/60">
                        <span class="text-slate-500 font-medium">{{ __('Amount') }}:</span>
                        <span id="delete-expense-amount" class="font-bold text-red-600 text-sm"></span>
                    </div>
                </div>

                <p class="mt-3 text-xs leading-relaxed text-slate-600">
                    {{ __('This action will permanently delete this expense and remove its amount from the ledger totals. Please enter your password to confirm.') }}
                </p>

                <form id="delete-expense-form" method="POST" action="" class="mt-4">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="password_confirmation_action" id="delete-expense-password-input">

                    <label class="block text-left">
                        <span class="text-xs font-semibold text-slate-700">{{ __('Your password') }} <span class="text-red-500">*</span></span>
                        <input id="delete-expense-password" type="password" required autocomplete="current-password"
                               placeholder="••••••••"
                               class="mt-1.5 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-red-500 focus:ring-red-500">
                    </label>

                    <div class="mt-6 flex justify-end gap-2.5">
                        <button type="button" onclick="closeDeleteExpenseModal()" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 transition">
                            {{ __('Cancel') }}
                        </button>
                        <button type="button" id="confirm-delete-expense-btn" onclick="submitDeleteExpenseModal()" class="inline-flex items-center gap-1.5 rounded-lg bg-red-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-800 transition">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                            </svg>
                            <span>{{ __('Delete') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            function openDeleteExpenseModal(url, desc, amount) {
                const modal = document.getElementById('delete-expense-modal');
                const form = document.getElementById('delete-expense-form');
                const descEl = document.getElementById('delete-expense-desc');
                const amountEl = document.getElementById('delete-expense-amount');
                const password = document.getElementById('delete-expense-password');

                form.action = url;
                descEl.textContent = desc;
                amountEl.textContent = amount;
                password.value = '';

                modal.classList.remove('hidden');
                modal.classList.add('flex');
                setTimeout(() => password.focus(), 50);
            }

            function closeDeleteExpenseModal() {
                const modal = document.getElementById('delete-expense-modal');
                const password = document.getElementById('delete-expense-password');
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                password.value = '';
            }

            function submitDeleteExpenseModal() {
                const password = document.getElementById('delete-expense-password');
                if (!password.value.trim()) {
                    password.focus();
                    return;
                }
                const form = document.getElementById('delete-expense-form');
                document.getElementById('delete-expense-password-input').value = password.value;
                const btn = document.getElementById('confirm-delete-expense-btn');
                btn.disabled = true;
                btn.classList.add('opacity-70', 'cursor-not-allowed');
                form.submit();
            }

            document.addEventListener('DOMContentLoaded', () => {
                const password = document.getElementById('delete-expense-password');
                if (password) {
                    password.addEventListener('keydown', (e) => {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            submitDeleteExpenseModal();
                        }
                    });
                }
            });
        </script>
    @endif
</x-app-layout>
