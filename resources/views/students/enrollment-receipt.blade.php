<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('Enrollment receipt') }}</title>
    @php
        $logoPath = public_path('images/gut-logo.png');
        $logoB64  = file_exists($logoPath) ? 'data:image/png;base64,'.base64_encode(file_get_contents($logoPath)) : null;
        $qrData   = $student->qr_token ?: $student->student_number;
        $qrSvg    = 'data:image/svg+xml;base64,'.base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(90)->margin(0)->generate($qrData));
    @endphp
    <style>
        @page { margin: 8mm 5mm; }
        body { width: 302px; margin: auto; font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 13px; color: #000; }
        .center { text-align: center; }
        .line { border-top: 1px dashed #000; margin: 9px 0; }
        .row { margin: 6px 0; display: flex; justify-content: space-between; }
        .small { font-size: 11px; color: #000; }
        .amount-box { border: 2px solid #000; border-radius: 4px; margin: 10px 0; padding: 8px 6px; }
        .amount-row { display: flex; justify-content: space-between; margin: 4px 0; }
        .amount-row.total { font-size: 17px; font-weight: bold; border-top: 1px solid #000; padding-top: 5px; margin-top: 5px; }
        .amount-row.remaining { font-size: 16px; font-weight: bold; }
        .badge { display: inline-block; padding: 2px 7px; border-radius: 4px; font-weight: bold; font-size: 11px; }
        .badge-paid    { background: #dcfce7; color: #14532d; border: 1px solid #166534; }
        .badge-unpaid  { background: #fee2e2; color: #7f1d1d; border: 1px solid #991b1b; }
        .badge-partial { background: #fef3c7; color: #78350f; border: 1px solid #92400e; }
    </style>
</head>
<body>

<div class="center">
    @if($logoB64)
        <img src="{{ $logoB64 }}" style="height:50px; margin-bottom:3px;" alt="GUT Center">
        <br>
    @endif
    <strong style="font-size:16px; letter-spacing:1px;">GUT CENTER</strong><br>
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
    <div class="amount-box">
        <div class="amount-row"><span>{{ __('Total') }} :</span> <span>{{ number_format((float)$registrationInvoice->amount_due, 0, ',', ' ') }} Ar</span></div>
        <div class="amount-row"><span>{{ __('Paid') }} :</span> <span>{{ number_format((float)$registrationInvoice->amount_paid, 0, ',', ' ') }} Ar</span></div>
        <div class="amount-row remaining"><span>{{ __('Remaining') }} :</span> <span>{{ number_format(max(0, (float)$registrationInvoice->amount_due - (float)$registrationInvoice->amount_paid), 0, ',', ' ') }} Ar</span></div>
    </div>
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
    <div class="amount-box">
        @foreach($monthlyPayments as $fee)
            <div class="amount-row"><span>{{ $fee->fee_month->locale(app()->getLocale())->translatedFormat('F Y') }} :</span> <span>{{ number_format((float)$fee->amount, 0, ',', ' ') }} Ar</span></div>
        @endforeach
        <div class="amount-row total"><span>{{ __('Total') }} :</span> <span>{{ number_format($monthlyPayments->sum('amount'), 0, ',', ' ') }} Ar</span></div>
    </div>
    <div class="row" style="margin-top: 4px;">
        <span><strong>{{ __('Status') }} :</strong></span>
        <span class="badge badge-paid">{{ __('Paid') }}</span>
    </div>

@else
    @if($schoolClass && $schoolClass->fee_mode === 'monthly' && (float)$schoolClass->monthly_fee_amount > 0)
        <div style="margin: 4px 0;"><strong>{{ __('Monthly school fees') }}</strong></div>
        <div class="amount-box">
            <div class="amount-row total"><span>{{ __('Monthly amount') }} :</span> <span>{{ number_format((float)$schoolClass->monthly_fee_amount, 0, ',', ' ') }} Ar</span></div>
        </div>
        <div class="row" style="margin-top: 4px;">
            <span><strong>{{ __('Status') }} :</strong></span>
            <span class="badge badge-unpaid">{{ __('None (Unpaid)') }}</span>
        </div>
    @else
        <div class="row"><span>{{ __('No payment recorded at enrollment.') }}</span></div>
    @endif
@endif

<div class="line"></div>

<div class="center" style="margin: 6px 0;">
    <img src="{{ $qrSvg }}" style="width:80px; height:80px;" alt="QR">
    <br><span class="small">{{ $student->student_number }}</span>
</div>

<div class="line"></div>

<div class="center small">
    <strong>GUT Center</strong> &mdash; Tél : 032 77 36 680<br>
    {{ __('Thank you.') }}
</div>

</body>
</html>
