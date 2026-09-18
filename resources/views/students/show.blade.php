<x-app-layout>
<x-slot name="header"><h2 class="font-semibold text-xl">{{ __('Student record') }}</h2></x-slot>
<style>
    .student-record-card { position:relative; }
    .student-record-avatar { position:absolute; top:22px; left:24px; width:54px; height:54px; display:flex; align-items:center; justify-content:center; overflow:hidden; border-radius:9999px; background:#047857; color:#fff; font-size:1.35rem; font-weight:700; box-shadow:0 2px 8px rgba(15,23,42,.18); }
    .student-record-avatar img { width:100%; height:100%; object-fit:cover; }
    .student-record-avatar + .flex.flex-wrap.justify-between.gap-4 > div:first-child { padding-left:70px; }
</style>
<div class="mx-auto max-w-5xl py-8 sm:px-6 lg:px-8"><div class="student-record-card rounded-xl bg-white p-6 shadow">
<div class="student-record-avatar">@if($studentPhotoUrl)<img src="{{ $studentPhotoUrl }}" alt="{{ __('Student photo') }}">@else{{ mb_strtoupper(mb_substr(trim($student->first_name ?: '?'), 0, 1)) }}@endif</div>
<div class="flex flex-wrap justify-between gap-4"><div><p class="text-2xl font-bold">{{ $student->first_name }} {{ $student->last_name }}</p><p class="text-gray-500">{{ $student->student_number }}</p></div><div class="flex flex-wrap items-center gap-2">@can('update', $student)<a href="{{ route('students.edit', $student) }}" class="inline-flex h-8 items-center rounded-lg border border-emerald-600 px-3 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-50">{{ __('Edit') }}</a>@endcan<button type="button" onclick="openStudentCardModal()" class="inline-flex h-8 items-center rounded-lg bg-emerald-700 px-3 text-xs font-semibold text-white shadow-sm transition hover:bg-emerald-800">{{ __('Student card') }}</button><a href="{{ route('students.index') }}" class="inline-flex h-8 items-center rounded-lg border border-slate-300 px-3 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">{{ __('Back') }}</a></div></div>
<div class="mt-6 grid gap-5 md:grid-cols-2"><div><h3 class="font-semibold">{{ __('Enrollment') }}</h3>@foreach($student->enrollments as $enrollment)<p>{{ $enrollment->schoolClass->name }} — {{ $enrollment->schoolClass->academicYear->name }}</p>@endforeach</div><div><h3 class="font-semibold">{{ __('Guardian') }}</h3>@foreach($student->guardians as $guardian)<p>{{ $guardian->first_name }} {{ $guardian->last_name }} · {{ $guardian->phone }}</p>@endforeach</div></div>
@if($canViewFees && $trainingClass?->fee_mode !== 'registration_only')<section class="mt-8 border-t pt-6"><div class="flex flex-wrap items-center justify-between gap-3"><div><div class="flex items-center gap-3"><h3 class="font-semibold">{{ __('Monthly school fees') }}</h3><button type="button" onclick="openReceiptModal('{{ route('students.enrollment-receipt-preview', $student) }}', '{{ route('students.enrollment-receipt', $student) }}')" class="inline-flex h-7 items-center rounded-lg border border-emerald-600 px-2.5 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-50 shadow-sm">{{ __('Enrollment receipt') }}</button></div><p class="text-sm text-gray-500">{{ __('Monthly amount') }}: {{ $student->monthly_fee_amount !== null ? number_format((float) $student->monthly_fee_amount, 0, ',', ' ').' Ar' : '—' }}</p></div>@can('update', $student)<a href="{{ route('students.edit', $student) }}" class="text-sm text-emerald-700">{{ __('Edit monthly fee') }}</a>@endcan</div><div class="mt-4 overflow-x-auto"><table class="w-full text-left text-sm"><thead><tr class="border-b"><th class="p-2">{{ __('Month') }}</th><th class="p-2">{{ __('Amount') }}</th><th class="p-2">{{ __('Status') }}</th><th class="p-2">{{ __('Action') }}</th></tr></thead><tbody>@foreach($months as $item)@php($paid = (bool) $item['fee']?->paid_at)<tr class="border-b {{ $paid ? 'bg-emerald-50' : '' }}"><td class="p-2">{{ $item['date']->translatedFormat('F Y') }}</td><td class="p-2">{{ $item['amount'] !== null ? number_format((float) $item['amount'], 0, ',', ' ').' Ar' : '—' }}</td><td class="p-2">{{ $paid ? __('Paid') : __('Unpaid') }}</td><td class="p-2">@if($paid && auth()->user()->hasRole('Admin'))<form method="POST" action="{{ route('students.monthly-fees.toggle', $student) }}" class="inline">@csrf @method('PATCH')<input type="hidden" name="month" value="{{ $item['date']->format('Y-m') }}"><button type="button" onclick="askFeeConfirmation(this.closest('form'), '{{ __('Cancel payment') }}')" class="text-sm text-gray-400 hover:text-red-700">{{ __('Uncheck') }}</button></form>@elseif(!$paid && auth()->user()->can('payments.manage'))<form method="POST" action="{{ route('students.monthly-fees.toggle', $student) }}" class="inline">@csrf @method('PATCH')<input type="hidden" name="month" value="{{ $item['date']->format('Y-m') }}"><label class="inline-flex items-center gap-2"><input type="checkbox" onchange="askFeeConfirmation(this.closest('form'), '{{ __('Confirm payment') }}')"><span>{{ __('Mark as paid') }}</span></label></form>@elseif($paid)<span class="text-gray-400">{{ __('Paid') }}</span>@else<span class="text-gray-400">—</span>@endif</td></tr>@endforeach</tbody></table></div></section>@endif
@if($registrationInvoice)<section class="mt-6 rounded-xl border border-amber-200 bg-amber-50 p-4"><h3 class="font-semibold text-amber-900">{{ __('Registration fee') }}</h3><p class="mt-1 text-sm">{{ __('Total') }} : {{ number_format((float)$registrationInvoice->amount_due,0,',',' ') }} Ar · {{ __('Paid') }} : {{ number_format((float)$registrationInvoice->amount_paid,0,',',' ') }} Ar · {{ __('Remaining') }} : {{ number_format(max(0,(float)$registrationInvoice->amount_due-(float)$registrationInvoice->amount_paid),0,',',' ') }} Ar</p><a class="registration-receipt-link mt-2 inline-block text-sm font-medium text-emerald-700" href="{{ route('school-fees.registration.receipt', [$trainingClass, $student]) }}">{{ __('View receipt / invoice') }}</a></section>@endif
@if($canViewFees)
<script>
    function openReceiptModal(previewUrl, pdfUrl) {
        const modal = document.getElementById('receipt-modal');
        document.getElementById('receipt-preview').src = previewUrl;
        document.getElementById('receipt-download').href = pdfUrl + (pdfUrl.includes('?') ? '&' : '?') + 'download=1';
        modal.dataset.pdfUrl = pdfUrl;
        modal.classList.remove('hidden'); modal.classList.add('flex');
        document.body.classList.add('overflow-hidden');
    }
    function closeReceiptModal() {
        const modal = document.getElementById('receipt-modal');
        modal.classList.add('hidden'); modal.classList.remove('flex');
        document.getElementById('receipt-preview').src = 'about:blank';
        document.body.classList.remove('overflow-hidden');
    }
    function printReceipt() {
        const frame = document.getElementById('receipt-print-frame');
        frame.onload = () => setTimeout(() => { frame.contentWindow?.focus(); frame.contentWindow?.print(); }, 350);
        frame.src = document.getElementById('receipt-modal').dataset.pdfUrl;
    }
    document.addEventListener('DOMContentLoaded', () => {
        const links = @json($receiptUrls);
        document.querySelectorAll('section.mt-8 table tbody tr').forEach((row, index) => {
            if (!links[index]) return;
            const cell = row.querySelector('td:last-child');
            const receipt = document.createElement('button');
            receipt.type = 'button';
            receipt.className = 'ml-3 inline-block rounded bg-slate-800 px-3 py-1.5 text-xs text-white hover:bg-slate-700';
            receipt.textContent = '{{ __('receipt.button') }}';
            receipt.addEventListener('click', () => openReceiptModal(links[index].preview, links[index].pdf));
            cell.appendChild(receipt);
        });
        document.querySelectorAll('.registration-receipt-link').forEach(link => link.addEventListener('click', event => {
            event.preventDefault();
            openReceiptModal(link.href + '-preview', link.href);
        }));
    });
</script>
@endif
</div></div>
@if($canViewFees)
<style>
    /* Ensure receipt modal header is always above sticky nav on mobile */
    @media (max-width: 639px) {
        #receipt-modal { align-items: flex-start; padding-top: 4.5rem; }
    }
</style>
<div id="receipt-modal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/60 p-3 sm:p-6" role="dialog" aria-modal="true" aria-labelledby="receipt-modal-title">
    <div class="flex w-full max-w-xs flex-col overflow-hidden rounded-2xl bg-white shadow-2xl" style="width: 310px; height: min(78vh, 680px);">
        <div class="flex shrink-0 items-center justify-between border-b border-slate-100 bg-white px-4 py-3">
            <h3 id="receipt-modal-title" class="font-semibold text-slate-800">{{ __('receipt.button') }}</h3>
            <button type="button" onclick="closeReceiptModal()" class="flex items-center gap-1 rounded-lg px-3 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-900 active:scale-95 transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                {{ __('Close') }}
            </button>
        </div>
        <iframe id="receipt-preview" title="{{ __('Receipt preview') }}" class="min-h-0 flex-1 bg-slate-100" src="about:blank"></iframe>
        <div class="flex flex-wrap justify-end gap-3 border-t bg-slate-50 px-4 py-3">
            <button type="button" onclick="printReceipt()" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-white">{{ __('Print') }}</button>
            <a id="receipt-download" href="#" class="rounded-lg bg-emerald-700 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-800">{{ __('Download') }}</a>
        </div>
    </div>
</div>
<iframe id="receipt-print-frame" class="hidden" title="{{ __('Receipt print') }}" src="about:blank"></iframe>
@endif
<div id="fee-confirmation" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4"><div class="w-full max-w-sm rounded-xl bg-white p-6 shadow-xl"><h3 id="fee-title" class="text-lg font-semibold"></h3><p class="mt-2 text-sm text-gray-600">{{ __('Please confirm this monthly school fee action.') }}</p><div class="mt-5 flex justify-end gap-3"><button type="button" onclick="closeFeeConfirmation()" class="rounded px-4 py-2">{{ __('Cancel') }}</button><button type="button" onclick="submitFeeConfirmation()" class="rounded bg-emerald-700 px-4 py-2 text-white">{{ __('Confirm') }}</button></div></div></div>
<script>let feeForm=null,feeBox=null,feePreviousState=false;function openFeeConfirmation(form,title,box){feeForm=form;feeBox=box;feePreviousState=box?!box.checked:false;document.getElementById('fee-title').textContent=title;document.getElementById('fee-confirmation').classList.remove('hidden');document.getElementById('fee-confirmation').classList.add('flex')}function closeFeeConfirmation(){if(feeBox)feeBox.checked=feePreviousState;document.getElementById('fee-confirmation').classList.add('hidden');document.getElementById('fee-confirmation').classList.remove('flex');feeForm=null;feeBox=null}function submitFeeConfirmation(){if(feeForm)feeForm.submit()}</script>
<div id="fee-amount-required" class="fixed inset-0 z-[60] hidden items-center justify-center bg-black/40 p-4"><div class="w-full max-w-sm rounded-xl bg-white p-6 shadow-xl"><h3 class="text-lg font-semibold">{{ __('Monthly fee not defined') }}</h3><p class="mt-2 text-sm text-gray-600">{{ __('Define the monthly school fee amount before marking a month as paid.') }}</p><div class="mt-5 flex justify-end gap-3"><button type="button" onclick="closeAmountRequired()" class="rounded px-4 py-2">{{ __('Cancel') }}</button>@can('update', $student)<a href="{{ route('students.edit', $student) }}" class="rounded bg-emerald-700 px-4 py-2 text-white">{{ __('Set amount') }}</a>@endcan</div></div></div>
<script>const monthlyFeeAmount={{ (float) ($student->monthly_fee_amount ?? 0) }};function askFeeConfirmation(form,title,box=null){box=box||form.querySelector('input[type=checkbox]');if(box&&box.checked&&monthlyFeeAmount<=0){box.checked=false;document.getElementById('fee-amount-required').classList.remove('hidden');document.getElementById('fee-amount-required').classList.add('flex');return}openFeeConfirmation(form,title,box)}function closeAmountRequired(){document.getElementById('fee-amount-required').classList.add('hidden');document.getElementById('fee-amount-required').classList.remove('flex')}</script>
@if(auth()->user()->hasRole('Admin'))
<div class="mx-auto mb-8 max-w-5xl px-4 text-right sm:px-6 lg:px-8"><button id="open-student-delete" type="button" class="rounded border border-red-300 px-4 py-2 text-red-700">{{ __('Delete student permanently') }}</button><form id="student-delete-form" method="POST" action="{{ route('students.destroy-permanently', $student) }}">@csrf @method('DELETE')</form></div>
<div id="student-delete-modal" class="fixed inset-0 z-[200] hidden items-center justify-center bg-black/50 p-4"><div class="w-full max-w-sm rounded-xl bg-white p-6 shadow-2xl"><h3 class="text-lg font-semibold text-red-700">{{ __('Delete student permanently') }}</h3><p class="mt-2 text-sm text-gray-600">{{ __('This permanently deletes the student record and cannot be undone.') }}</p><label class="mt-4 block text-sm">{{ __('Administrator password') }}<input id="student-delete-password" type="password" class="mt-1 w-full rounded border-gray-300"></label><div class="mt-5 flex justify-end gap-3"><button id="close-student-delete" type="button" class="rounded px-4 py-2">{{ __('Cancel') }}</button><button id="confirm-student-delete" type="button" class="rounded bg-red-700 px-4 py-2 text-white">{{ __('Delete permanently') }}</button></div></div></div>
<script>document.addEventListener('DOMContentLoaded',()=>{const modal=document.getElementById('student-delete-modal'),password=document.getElementById('student-delete-password');document.getElementById('open-student-delete')?.addEventListener('click',()=>{modal.classList.remove('hidden');modal.classList.add('flex');password.focus()});document.getElementById('close-student-delete')?.addEventListener('click',()=>{modal.classList.add('hidden');modal.classList.remove('flex');password.value=''});document.getElementById('confirm-student-delete')?.addEventListener('click',()=>{const form=document.getElementById('student-delete-form');let input=form.querySelector('[name=password_confirmation_action]');if(!input){input=document.createElement('input');input.type='hidden';input.name='password_confirmation_action';form.appendChild(input)}input.value=password.value;form.submit()})})</script>
@endif
<div id="student-card-modal" class="fixed inset-0 z-[160] hidden items-center justify-center bg-black/60 p-4 backdrop-blur-sm" aria-hidden="true">
    <div class="flex w-full max-w-4xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl" style="height:min(88vh,720px)">
        <div class="flex items-center justify-between border-b border-slate-200 bg-white px-5 py-3.5">
            <div class="flex items-center gap-2">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700 text-base font-bold">🪪</span>
                <h3 class="font-bold text-slate-900 text-base sm:text-lg">{{ __('Student card') }} — {{ $student->first_name }} {{ $student->last_name }}</h3>
            </div>
            <button type="button" onclick="closeStudentCardModal()" class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100 hover:text-slate-800 transition">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <iframe id="student-card-iframe" class="min-h-0 flex-1 w-full bg-slate-50" title="{{ __('Student card') }}" src="about:blank"></iframe>
        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 bg-slate-50 px-5 py-3">
            <span class="text-xs text-slate-500">{{ __('Recto & Verso view') }}</span>
            <div class="flex items-center gap-2">
                <button type="button" onclick="closeStudentCardModal()" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-100 transition">{{ __('Close') }}</button>
                <a href="{{ route('students.card', $student) }}" class="inline-flex items-center gap-2 rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-800 transition">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    {{ __('Download') }}
                </a>
            </div>
        </div>
    </div>
</div>
<script>
const studentCardPreviewUrl = @json(route('students.card-preview', $student));
function openStudentCardModal() {
    const modal = document.getElementById('student-card-modal');
    const iframe = document.getElementById('student-card-iframe');
    if (!modal || !iframe) return;
    if (iframe.src === 'about:blank' || !iframe.src) {
        iframe.src = studentCardPreviewUrl;
    }
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.classList.add('overflow-hidden');
}
function closeStudentCardModal() {
    const modal = document.getElementById('student-card-modal');
    if (!modal) return;
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.classList.remove('overflow-hidden');
}
document.getElementById('student-card-modal')?.addEventListener('click', event => {
    if (event.target.id === 'student-card-modal') closeStudentCardModal();
});
</script>
</x-app-layout>
