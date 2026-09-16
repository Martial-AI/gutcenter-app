<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="utf-8">
@php
    $logoPath = public_path('images/gut-logo.png');
    $logoB64  = file_exists($logoPath) ? 'data:image/png;base64,'.base64_encode(file_get_contents($logoPath)) : null;
    $student  = $fee->student;
    $qrData   = $student->qr_token ?: $student->student_number;
    $qrSvg    = 'data:image/svg+xml;base64,'.base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(90)->margin(0)->generate($qrData));
@endphp
<style>
    @page { margin: 8mm 5mm; }
    body { width: 302px; margin: 0 auto; font-family: DejaVu Sans, sans-serif; font-size: 13px; color: #000; }
    .center { text-align: center; }
    .line { border-top: 1px dashed #000; margin: 9px 0; }
    .row { margin: 6px 0; }
    .small { font-size: 11px; color: #000; }
    .amount-box { border: 2px solid #000; border-radius: 4px; text-align: center; margin: 10px 0; padding: 8px 4px; }
    .amount-label { font-size: 11px; margin-bottom: 4px; }
    .amount-value { font-size: 23px; font-weight: bold; }
</style>
</head>
<body>

<div class="center">
    @if($logoB64)
        <img src="{{ $logoB64 }}" style="height:50px; margin-bottom:3px;" alt="GUT Center">
        <br>
    @endif
    <strong style="font-size:16px; letter-spacing:1px;">GUT CENTER</strong><br>
    <span class="small">{{ __('receipt.title') }}</span>
</div>

<div class="line"></div>

<div class="row"><strong>{{ __('receipt.number') }}:</strong> {{ $fee->receipt_number }}</div>
<div class="row"><strong>{{ __('receipt.date') }}:</strong> {{ $fee->paid_at?->format('d/m/Y H:i') }}</div>
<div class="row"><strong>{{ __('Student') }}:</strong> {{ $fee->student->first_name }} {{ $fee->student->last_name }}</div>
<div class="row"><strong>{{ __('Student number') }}:</strong> {{ $fee->student->student_number }}</div>
<div class="row"><strong>{{ __('Class') }}:</strong> {{ $fee->student->enrollments->firstWhere('status', 'active')?->schoolClass?->name ?? '—' }}</div>
<div class="row"><strong>{{ __('Month') }}:</strong> {{ $fee->fee_month->locale(app()->getLocale())->translatedFormat('F Y') }}</div>

<div class="line"></div>

<div class="amount-box">
    <div class="amount-label">{{ __('Amount paid') }}</div>
    <div class="amount-value">{{ number_format((float) $fee->amount, 0, ',', ' ') }} Ar</div>
</div>

<div class="center">
    <strong style="font-size:15px;">{{ __('Paid') }}</strong><br>
    <span class="small">{{ __('receipt.collected_by') }}: {{ $fee->paidBy?->name ?? '—' }}</span>
</div>

<div class="line"></div>

<div class="center" style="margin: 6px 0;">
    <img src="{{ $qrSvg }}" style="width:80px; height:80px;" alt="QR">
    <br><span class="small">{{ $fee->student->student_number }}</span>
</div>

<div class="line"></div>

<div class="center small">
    <strong>GUT Center</strong> &mdash; Tél : 032 77 36 680<br>
    {{ __('receipt.thank_you') }}
</div>

</body>
</html>
