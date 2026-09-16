<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="utf-8">
@php
    $logoPath = public_path('images/gut-logo.png');
    $logoB64  = file_exists($logoPath) ? 'data:image/png;base64,'.base64_encode(file_get_contents($logoPath)) : null;
    $qrData   = $student->qr_token ?: $student->student_number;
    $qrSvg    = 'data:image/svg+xml;base64,'.base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(90)->margin(0)->generate($qrData));
    $paidAmt  = (float)$invoice->amount_paid;
    $dueAmt   = (float)$invoice->amount_due;
    $remaining = max(0, $dueAmt - $paidAmt);
@endphp
<style>
    @page { margin: 8mm 5mm; }
    body { width: 302px; margin: auto; font-family: DejaVu Sans, sans-serif; font-size: 13px; color: #000; }
    .center { text-align: center; }
    .line { border-top: 1px dashed #000; margin: 9px 0; }
    .row { margin: 6px 0; }
    .small { font-size: 11px; color: #000; }
    .amount-box { border: 2px solid #000; border-radius: 4px; margin: 10px 0; padding: 8px 6px; }
    .amount-row { display: flex; justify-content: space-between; margin: 4px 0; }
    .amount-paid { font-size: 22px; font-weight: bold; text-align: center; padding: 8px 0 4px; }
    .amount-remaining { font-size: 18px; font-weight: bold; display: flex; justify-content: space-between; border-top: 1px solid #000; padding-top: 5px; margin-top: 5px; }
</style>
</head>
<body>

<div class="center">
    @if($logoB64)
        <img src="{{ $logoB64 }}" style="height:50px; margin-bottom:3px;" alt="GUT Center">
        <br>
    @endif
    <strong style="font-size:16px; letter-spacing:1px;">GUT CENTER</strong><br>
    <span class="small">{{ __('Registration fee receipt / invoice') }}</span>
</div>

<div class="line"></div>

<div class="row"><strong>{{ __('Date') }} :</strong> {{ $invoice->updated_at?->format('d/m/Y H:i') }}</div>
<div class="row"><strong>{{ __('Student') }} :</strong> {{ $student->first_name }} {{ $student->last_name }}</div>
<div class="row"><strong>{{ __('Student number') }} :</strong> {{ $student->student_number }}</div>
<div class="row"><strong>{{ __('Class') }} :</strong> {{ $schoolClass->name }}</div>

<div class="line"></div>

<div class="amount-box">
    <div class="amount-row"><span>{{ __('Total due') }} :</span> <span>{{ number_format($dueAmt, 0, ',', ' ') }} Ar</span></div>
    <div style="text-align:center; font-size:11px; margin: 4px 0;">{{ $paidAmt > 0 ? __('Registration fee collected') : __('Registration fee due') }}</div>
    <div class="amount-paid">{{ number_format($paidAmt, 0, ',', ' ') }} Ar</div>
    <div class="amount-remaining">
        <span>{{ __('Remaining') }} :</span>
        <span>{{ number_format($remaining, 0, ',', ' ') }} Ar</span>
    </div>
</div>

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
