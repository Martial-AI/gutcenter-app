<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('Enrollment receipt') }}</title>
    <style>
        @page { margin: 6mm 4mm; }
        body { width: 290px; margin: auto; font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 10.5px; color: #1e293b; }
        .center { text-align: center; }
        .line { border-top: 1px dashed #64748b; margin: 8px 0; }
        .row { margin: 5px 0; display: flex; justify-content: space-between; }
        .small { font-size: 8.5px; color: #64748b; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 4px; font-weight: bold; font-size: 9px; }
        .badge-paid { background: #dcfce7; color: #166534; }
        .badge-unpaid { background: #fee2e2; color: #991b1b; }
        .badge-partial { background: #fef3c7; color: #92400e; }
    </style>
</head>
<body>
    <div class="center">
        <strong style="font-size: 15px; color: #0f172a;">GUT Center</strong><br>
        <span class="small">{{ __('Enrollment receipt / record') }}</span>
    </div>
    <div class="line"></div>
    <div class="row"><span><strong>{{ __('Date') }} :</strong></span> <span>{{ now()->format('d/m/Y H:i') }}</span></div>
    <div class="row"><span><strong>{{ __('Student') }} :</strong></span> <span>{{ $student->first_name }} {{ $student->last_name }}</span></div>
    <div class="row"><span><strong>{{ __('Student number') }} :</strong></span> <span>{{ $student->student_number }}</span></div>
    <div class="row"><span><strong>{{ __('Class') }} :</strong></span> <span>{{ $schoolClass?->name ?? '—' }}</span></div>
    <div class="line"></div>

    @if($registrationInvoice)
        <div style="margin: 4px 0;"><strong>{{ __('Registration fee') }}</strong></div>
        <div class="row"><span>{{ __('Total') }} :</span> <span>{{ number_format((float)$registrationInvoice->amount_due, 0, ',', ' ') }} Ar</span></div>
        <div class="row"><span>{{ __('Paid') }} :</span> <span>{{ number_format((float)$registrationInvoice->amount_paid, 0, ',', ' ') }} Ar</span></div>
        <div class="row"><span>{{ __('Remaining') }} :</span> <span>{{ number_format(max(0, (float)$registrationInvoice->amount_due - (float)$registrationInvoice->amount_paid), 0, ',', ' ') }} Ar</span></div>
        <div class="row" style="margin-top: 4px;">
            <span><strong>{{ __('Status') }} :</strong></span>
            @if((float)$registrationInvoice->amount_paid <= 0)
                <span class="badge badge-unpaid">{{ __('Unpaid') }}</span>
            @elseif((float)$registrationInvoice->amount_paid < (float)$registrationInvoice->amount_due)
                <span class="badge badge-partial">{{ __('Partial payment') }}</span>
            @else
                <span class="badge badge-paid">{{ __('Paid in full') }}</span>
            @endif
        </div>
    @elseif($monthlyPayments->isNotEmpty())
        <div style="margin: 4px 0;"><strong>{{ __('School fees collected at enrollment') }}</strong></div>
        @foreach($monthlyPayments as $fee)
            <div class="row"><span>{{ $fee->fee_month->locale(app()->getLocale())->translatedFormat('F Y') }} :</span> <span>{{ number_format((float)$fee->amount, 0, ',', ' ') }} Ar</span></div>
        @endforeach
        <div class="row" style="margin-top: 4px;">
            <span><strong>{{ __('Status') }} :</strong></span>
            <span class="badge badge-paid">{{ __('Paid') }}</span>
        </div>
    @else
        @if($schoolClass && $schoolClass->fee_mode === 'monthly' && (float)$schoolClass->monthly_fee_amount > 0)
            <div style="margin: 4px 0;"><strong>{{ __('Monthly school fees') }}</strong></div>
            <div class="row"><span>{{ __('Monthly amount') }} :</span> <span>{{ number_format((float)$schoolClass->monthly_fee_amount, 0, ',', ' ') }} Ar</span></div>
            <div class="row" style="margin-top: 4px;">
                <span><strong>{{ __('Status') }} :</strong></span>
                <span class="badge badge-unpaid">{{ __('None (Unpaid)') }}</span>
            </div>
        @else
            <div class="row"><span>{{ __('No payment recorded at enrollment.') }}</span></div>
        @endif
    @endif

    <div class="line"></div>
    <div class="center small" style="margin-top: 6px;">{{ __('Thank you.') }}</div>
</body>
</html>
