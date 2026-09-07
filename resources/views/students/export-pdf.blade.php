<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="UTF-8">
<title>{{ __('Student List') }} - {{ config('app.name', 'GUT Center') }}</title>
<style>
    @page {
        margin: 18px 22px 35px 22px;
    }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: 'DejaVu Sans', Arial, sans-serif;
        font-size: 9px;
        color: #1e293b;
        background: #ffffff;
    }

    /* ─── Header Table Layout (DomPDF reliable) ─── */
    .header-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 0;
    }
    .header-top {
        background-color: #1e3a8a; /* Deep Royal Navy Blue */
        border-bottom: 3px solid #be123c; /* Cardinal Red Accent Border */
    }
    .logo-cell {
        width: 68px;
        padding: 12px 10px 12px 14px;
        vertical-align: middle;
        text-align: center;
    }
    .logo-img {
        width: 52px;
        height: 52px;
        background-color: #ffffff;
        border-radius: 8px;
        padding: 3px;
        border: 2px solid #ffffff;
    }
    .header-info-cell {
        padding: 12px 10px;
        vertical-align: middle;
    }
    .doc-pill {
        display: inline-block;
        background-color: #be123c; /* Crimson Red badge */
        color: #ffffff;
        font-size: 7.5px;
        font-weight: bold;
        padding: 2px 7px;
        border-radius: 4px;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        margin-bottom: 3px;
    }
    .school-name {
        font-size: 16px;
        font-weight: 800;
        color: #ffffff;
        letter-spacing: 0.3px;
        line-height: 1.1;
    }
    .doc-title {
        font-size: 10.5px;
        font-weight: bold;
        color: #bfdbfe; /* Soft Light Blue */
        margin-top: 3px;
    }
    .gen-date {
        font-size: 7.5px;
        color: #93c5fd;
        margin-top: 2px;
    }

    .badge-cell {
        width: 130px;
        padding: 12px 14px 12px 10px;
        vertical-align: middle;
        text-align: right;
    }
    .stat-badge-box {
        background-color: #9f1239; /* Deep Crimson Red Card */
        border: 2px solid #ffffff;
        border-radius: 8px;
        padding: 6px 12px;
        text-align: center;
        width: 110px;
        float: right;
    }
    .badge-count {
        font-size: 22px;
        font-weight: 900;
        color: #ffffff;
        line-height: 1;
    }
    .badge-lbl {
        font-size: 7px;
        color: #fecdd3; /* Soft Light Red */
        text-transform: uppercase;
        font-weight: bold;
        letter-spacing: 0.5px;
        margin-top: 2px;
    }

    /* ─── Two-tone Bar (Blue / Red Mixed) ─── */
    .twotone-bar {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 12px;
    }
    .twotone-blue {
        background-color: #1e40af; /* Blue */
        color: #ffffff;
        padding: 5px 14px;
        font-size: 8px;
        font-weight: normal;
        width: 55%;
    }
    .twotone-blue strong {
        color: #ffffff;
    }
    .twotone-red {
        background-color: #be123c; /* Red */
        color: #ffffff;
        padding: 5px 14px;
        font-size: 8px;
        text-align: right;
        font-weight: normal;
        width: 45%;
    }
    .twotone-red strong {
        color: #ffffff;
    }

    /* ─── Summary KPI Cards Table ─── */
    .kpi-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 6px 0;
        margin-bottom: 14px;
    }
    .kpi-card {
        padding: 6px 8px;
        text-align: center;
        border-radius: 6px;
        border: 1px solid #cbd5e1;
    }
    .kpi-card-blue {
        background-color: #eff6ff;
        border-top: 3px solid #1e40af;
    }
    .kpi-card-slate {
        background-color: #f8fafc;
        border-top: 3px solid #475569;
    }
    .kpi-card-male {
        background-color: #eff6ff;
        border-top: 3px solid #2563eb;
    }
    .kpi-card-female {
        background-color: #fff1f2;
        border-top: 3px solid #be123c;
    }
    .kpi-val {
        font-size: 16px;
        font-weight: 800;
        line-height: 1.1;
    }
    .kpi-val-blue { color: #1e3a8a; }
    .kpi-val-slate { color: #1e293b; }
    .kpi-val-male { color: #1d4ed8; }
    .kpi-val-female { color: #9f1239; }
    .kpi-lbl {
        font-size: 7px;
        font-weight: bold;
        color: #64748b;
        text-transform: uppercase;
        margin-top: 2px;
        letter-spacing: 0.4px;
    }

    /* ─── Class Section ─── */
    .class-container {
        margin-bottom: 14px;
        page-break-inside: avoid;
    }
    .class-header-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 0;
    }
    .class-title-cell {
        background-color: #1e3a8a; /* Blue header */
        color: #ffffff;
        font-size: 10px;
        font-weight: 800;
        padding: 5px 10px;
        border-left: 4px solid #be123c; /* Red border accent */
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }
    .class-stat-cell {
        background-color: #1e3a8a;
        color: #fecdd3;
        font-size: 8px;
        text-align: right;
        padding: 5px 10px;
        font-weight: bold;
    }
    .class-badge-pill {
        display: inline-block;
        background-color: #be123c; /* Red pill */
        color: #ffffff;
        padding: 1px 7px;
        border-radius: 10px;
        font-size: 8px;
        font-weight: bold;
        margin-left: 5px;
    }

    /* ─── Students Table ─── */
    .students-table {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid #cbd5e1;
        margin-bottom: 2px;
    }
    .students-table thead tr {
        background-color: #0f172a; /* Slate 900 */
        color: #ffffff;
        border-bottom: 2px solid #be123c; /* Red border */
    }
    .students-table thead th {
        padding: 5px 7px;
        font-size: 7.5px;
        font-weight: bold;
        text-align: left;
        color: #ffffff;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }
    .students-table tbody tr {
        border-bottom: 1px solid #e2e8f0;
    }
    .students-table tbody tr.even {
        background-color: #f8fafc;
    }
    .students-table tbody tr.odd {
        background-color: #ffffff;
    }
    .students-table td {
        padding: 4.5px 7px;
        font-size: 8.5px;
        color: #1e293b;
        vertical-align: middle;
    }

    .row-index {
        width: 22px;
        text-align: center;
        color: #64748b;
        font-weight: bold;
        font-size: 7.5px;
    }
    .num-badge {
        display: inline-block;
        background-color: #eff6ff;
        border: 1px solid #bfdbfe;
        color: #1e40af;
        padding: 1px 5px;
        border-radius: 3px;
        font-size: 8px;
        font-weight: bold;
        font-family: monospace;
    }
    .student-name-cell {
        font-size: 8.5px;
    }
    .student-name-cell strong {
        color: #0f172a;
    }
    .gender-badge {
        display: inline-block;
        padding: 1px 5px;
        border-radius: 3px;
        font-size: 7.5px;
        font-weight: bold;
        text-align: center;
    }
    .gender-m {
        background-color: #dbeafe;
        color: #1e40af;
        border: 1px solid #93c5fd;
    }
    .gender-f {
        background-color: #ffe4e6;
        color: #9f1239;
        border: 1px solid #fecdd3;
    }
    .gender-none {
        color: #94a3b8;
    }

    /* ─── Footer Fixed ─── */
    .footer-fixed {
        position: fixed;
        bottom: -22px;
        left: -22px;
        right: -22px;
        height: 20px;
    }
    .footer-table {
        width: 100%;
        border-collapse: collapse;
    }
    .footer-blue {
        background-color: #1e3a8a;
        color: #bfdbfe;
        font-size: 7px;
        padding: 3px 18px;
        vertical-align: middle;
    }
    .footer-red {
        background-color: #9f1239;
        color: #fecdd3;
        font-size: 7px;
        text-align: right;
        padding: 3px 18px;
        vertical-align: middle;
        font-weight: bold;
    }
</style>
</head>
<body>

<!-- ── FIXED FOOTER ── -->
<div class="footer-fixed">
    <table class="footer-table">
        <tr>
            <td class="footer-blue">
                {{ config('app.name', 'GUT Center') }} &bull; {{ __('Student List') }} &bull; {{ __('Official & Confidential Document') }}
            </td>
            <td class="footer-red">
                {{ __('Issued on') }} {{ now()->format('d/m/Y H:i') }}
            </td>
        </tr>
    </table>
</div>

<!-- ── HEADER BANNER (Mixed Blue & Red Design) ── -->
<table class="header-table">
    <tr class="header-top">
        <td class="logo-cell">
            @if(!empty($logo))
                <img class="logo-img" src="{{ $logo }}" alt="Logo">
            @elseif(file_exists(public_path('images/gut-logo.png')))
                <img class="logo-img" src="{{ public_path('images/gut-logo.png') }}" alt="Logo">
            @else
                <div class="logo-img" style="line-height:48px;font-weight:bold;color:#1e3a8a;">GUT</div>
            @endif
        </td>
        <td class="header-info-cell">
            <div><span class="doc-pill">{{ __('Official Document') }}</span></div>
            <div class="school-name">{{ config('app.name', 'GUT Center') }}</div>
            <div class="doc-title">{{ __('LISTE OFFICIELLE DES ÉLÈVES PAR CLASSE') }}</div>
            <div class="gen-date">{{ __('Generated on') }} {{ now()->translatedFormat('l d F Y - H:i') }}</div>
        </td>
        <td class="badge-cell">
            <div class="stat-badge-box">
                <div class="badge-count">{{ $totalStudents }}</div>
                <div class="badge-lbl">{{ __('Total Students') }}</div>
            </div>
        </td>
    </tr>
</table>

<!-- ── TWO-TONE BLUE / RED INFO BAR ── -->
<table class="twotone-bar">
    <tr>
        <td class="twotone-blue">
            {{ __('Academic year') }} : <strong>{{ $academicYear ?: __('In progress') }}</strong>
            &nbsp;&bull;&nbsp;
            <strong>{{ count($grouped) }}</strong> {{ __('class(es) active(s)') }}
        </td>
        <td class="twotone-red">
            <strong>{{ $totalMale }}</strong> &#9794; {{ __('Male') }}
            &nbsp;&bull;&nbsp;
            <strong>{{ $totalFemale }}</strong> &#9792; {{ __('Female') }}
            &nbsp;&bull;&nbsp;
            <em>{{ __('Strictly confidential') }}</em>
        </td>
    </tr>
</table>

<!-- ── STATS CARDS ── -->
<table class="kpi-table">
    <tr>
        <td class="kpi-card kpi-card-blue" style="width: 25%;">
            <div class="kpi-val kpi-val-blue">{{ $totalStudents }}</div>
            <div class="kpi-lbl">{{ __('Total students') }}</div>
        </td>
        <td class="kpi-card kpi-card-slate" style="width: 25%;">
            <div class="kpi-val kpi-val-slate">{{ count($grouped) }}</div>
            <div class="kpi-lbl">{{ __('Classes') }}</div>
        </td>
        <td class="kpi-card kpi-card-male" style="width: 25%;">
            <div class="kpi-val kpi-val-male">{{ $totalMale }}</div>
            <div class="kpi-lbl">&#9794; {{ __('Male') }} ({{ $totalStudents > 0 ? round(($totalMale / $totalStudents) * 100) : 0 }}%)</div>
        </td>
        <td class="kpi-card kpi-card-female" style="width: 25%;">
            <div class="kpi-val kpi-val-female">{{ $totalFemale }}</div>
            <div class="kpi-lbl">&#9792; {{ __('Female') }} ({{ $totalStudents > 0 ? round(($totalFemale / $totalStudents) * 100) : 0 }}%)</div>
        </td>
    </tr>
</table>

<!-- ── LIST OF STUDENTS GROUPED BY CLASS ── -->
@forelse($grouped as $className => $students)
<div class="class-container">
    <table class="class-header-table">
        <tr>
            <td class="class-title-cell">
                {{ __('Classe') }} : {{ $className }}
            </td>
            <td class="class-stat-cell">
                {{ $students->where('gender', 'male')->count() }} &#9794; &nbsp;|&nbsp;
                {{ $students->where('gender', 'female')->count() }} &#9792;
                <span class="class-badge-pill">{{ count($students) }} {{ __('élèves') }}</span>
            </td>
        </tr>
    </table>

    <table class="students-table">
        <thead>
            <tr>
                <th style="width: 24px; text-align: center;">#</th>
                <th style="width: 85px;">{{ __('Student number') }}</th>
                <th>{{ __('Last name & First name') }}</th>
                <th style="width: 50px; text-align: center;">{{ __('Gender') }}</th>
                <th style="width: 85px;">{{ __('Birth date') }}</th>
                <th style="width: 90px;">{{ __('Phone') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($students as $idx => $student)
            <tr class="{{ $loop->even ? 'even' : 'odd' }}">
                <td class="row-index">{{ $idx + 1 }}</td>
                <td>
                    <span class="num-badge">{{ $student->student_number }}</span>
                </td>
                <td class="student-name-cell">
                    <strong>{{ mb_strtoupper($student->last_name) }}</strong> {{ $student->first_name }}
                </td>
                <td style="text-align: center;">
                    @if($student->gender === 'male')
                        <span class="gender-badge gender-m">&#9794; M</span>
                    @elseif($student->gender === 'female')
                        <span class="gender-badge gender-f">&#9792; F</span>
                    @else
                        <span class="gender-none">&mdash;</span>
                    @endif
                </td>
                <td>
                    {{ $student->birth_date ? \Carbon\Carbon::parse($student->birth_date)->format('d/m/Y') : '—' }}
                </td>
                <td>
                    {{ $student->phone ?: '—' }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center; color: #64748b; padding: 8px;">
                    {{ __('No student registered in this class.') }}
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@empty
<div style="text-align: center; padding: 40px; color: #64748b; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; margin-top: 20px;">
    <p style="font-size: 13px; font-weight: bold;">{{ __('No students found.') }}</p>
</div>
@endforelse

</body>
</html>
