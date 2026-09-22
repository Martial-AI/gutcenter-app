<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Services\ExpenseService;
use App\Services\SensitiveActivityNotifier;
use App\Services\TrashService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    /**
     * Display the expense ledger, including salary payments already recorded
     * through the account-management screen.
     */
    public function index(ExpenseService $expenses): View|RedirectResponse
    {
        if (! auth()->user()?->can('expenses.view')) {
            return redirect()->back()->with(
                'permission_denied',
                __('You do not have permission to view expenses.')
            );
        }

        // Salary payments are expenses as well. Synchronising here also brings
        // payments created before the expenses screen was introduced into the
        // ledger without creating duplicates.
        $expenses->synchronizePaidSalaryExpenses();

        return view('expenses.index', [
            'totalExpenses' => Expense::sum('amount'),
            'expenses' => Expense::query()
                ->with(['recordedBy', 'employeePayment.user'])
                ->orderByDesc('spent_at')
                ->orderByDesc('spent_on')
                ->orderByDesc('id')
                ->paginate(20),
        ]);
    }

    /**
     * Record a non-salary expense. Salary entries are created automatically
     * from employee payments and are intentionally not entered here.
     */
    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('expenses.create'), 403);

        $data = $request->validate([
            'category' => ['required', 'string', 'max:100'],
            'reason' => ['required', 'string', 'max:1000'],
            'amount' => ['required', 'numeric', 'gt:0', 'max:9999999999.99'],
            'spent_at' => ['required', 'date'],
        ]);

        $spentAt = Carbon::parse($data['spent_at']);
        $expense = Expense::create([
            'reference' => $this->nextReference(),
            'category' => $data['category'],
            'reason' => $data['reason'],
            'amount' => $data['amount'],
            'spent_on' => $spentAt->toDateString(),
            'spent_at' => $spentAt,
            'recorded_by' => $request->user()->id,
        ]);

        activity('dépenses')
            ->causedBy($request->user())
            ->performedOn($expense)
            ->log('Expense added: '.$expense->reason);

        SensitiveActivityNotifier::send(
            __('Expense added'),
            __('Expense added: :expense', ['expense' => $expense->reason]),
        );

        return back()->with('success', __('Expense added successfully.'));
    }

    /**
     * Delete an expense, remove its amount from totals, and record in audit log.
     * Requires user password confirmation.
     */
    public function destroy(Request $request, Expense $expense): RedirectResponse
    {
        abort_unless($request->user()?->can('expenses.delete') || $request->user()?->hasRole('Admin'), 403);

        $request->validate([
            'password_confirmation_action' => ['required', 'string'],
        ]);

        if (! Hash::check($request->input('password_confirmation_action'), $request->user()->password)) {
            throw ValidationException::withMessages([
                'password_confirmation_action' => __('The password is incorrect.'),
            ]);
        }

        $reason = $expense->reason;
        $amount = (float) $expense->amount;
        $formattedAmount = number_format($amount, 0, ',', ' ').' Ar';

        // If this expense is linked to an employee salary payment, reset payment status to pending
        // so it does not remain marked as paid after the expense is removed.
        if ($expense->employee_payment_id) {
            $expense->employeePayment?->update([
                'paid_at' => null,
                'status' => 'pending',
                'recorded_by' => null,
                'payment_method' => null,
                'payment_phone' => null,
                'transaction_id' => null,
                'bank_details' => null,
            ]);
        }

        TrashService::store('expense', $expense, 'Dépense : '.$reason.' ('.$formattedAmount.')');

        activity('dépenses')
            ->causedBy($request->user())
            ->log('Expense deleted: '.$reason);

        SensitiveActivityNotifier::send(
            __('Expense deleted'),
            __('Expense deleted: :expense (:amount)', [
                'expense' => $reason,
                'amount' => $formattedAmount,
            ]),
        );

        $expense->delete();

        return back()->with('success', __('Expense deleted successfully.'));
    }

    private function nextReference(): string
    {
        do {
            $reference = 'EXP-'.now()->format('YmdHis').'-'.Str::upper(Str::random(5));
        } while (Expense::where('reference', $reference)->exists());

        return $reference;
    }
}
