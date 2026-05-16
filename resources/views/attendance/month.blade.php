@extends('layouts.app')
@section('title', __('Chấm công tháng').' '.$month.'/'.$year)

@push('scripts')
<style>
    /* ===== B&W Excel-style attendance grid for print ===== */
    @media print {
        @page { size: A4 landscape; margin: 8mm 8mm; }

        html, body {
            background: #fff !important;
            color: #000 !important;
            font-family: 'IBM Plex Mono', Consolas, monospace !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .gz-nav, .gz-footer, .no-print, .alert { display: none !important; }
        main.container, .container { max-width: 100% !important; padding: 0 !important; }

        /* Tight masthead — keep month/title; drop the lede paragraph. */
        .gz-section-rule { margin: 0 0 3pt 0 !important; }
        .gz-section-rule::before, .gz-section-rule::after { border-color: #000 !important; }
        .gz-section-rule-text { color: #000 !important; }
        .gz-section-rule-text em { color: #000 !important; }
        .gz-section-title { font-size: 11pt !important; color: #000 !important; margin: 0 0 3pt 0 !important; }
        .gz-section-lede { display: none !important; }
        .gz-card-head { margin: 0 0 4pt 0 !important; }

        .gz-card {
            background: transparent !important;
            border: none !important;
            padding: 0 !important;
            margin: 0 !important;
            box-shadow: none !important;
        }

        .table-responsive { overflow: visible !important; max-height: none !important; }

        .gz-grid-table {
            min-width: auto !important;
            width: 100% !important;
            font-size: 8pt !important;
            font-family: 'IBM Plex Mono', Consolas, monospace !important;
            border-collapse: collapse !important;
            page-break-inside: auto !important;
        }
        /* Repeat headers on every printed page; rows are atomic. */
        .gz-grid-table thead { display: table-header-group !important; }
        .gz-grid-table thead.sticky-top { position: static !important; }
        .gz-grid-table tbody { page-break-inside: auto !important; }
        .gz-grid-table tr { page-break-inside: avoid !important; page-break-after: auto !important; }

        .gz-grid-table th, .gz-grid-table td {
            padding: 2pt 2pt !important;
            background: #fff !important;
            color: #000 !important;
            border: 0.5pt solid #000 !important;
            text-align: center !important;
            vertical-align: middle !important;
        }
        .gz-grid-table thead th {
            background: #d9d9d9 !important;
            font-weight: 700 !important;
            border: 0.6pt solid #000 !important;
        }
        /* Sunday columns shaded so they read at a glance even without color */
        .gz-grid-table .sunday-col,
        .gz-grid-table thead .sunday-col { background: #b8b8b8 !important; }
        .att-cell.att-sunday { background: #d9d9d9 !important; }

        /* Employee column: left-aligned, slightly wider */
        .gz-grid-table tbody td:first-child {
            text-align: left !important;
            padding: 2pt 4pt !important;
            font-family: 'IBM Plex Mono', Consolas, monospace !important;
        }
        .gz-grid-table tbody td:first-child strong { font-weight: 700 !important; color: #000 !important; }
        .gz-grid-table tbody td:first-child small {
            color: #000 !important;
            font-style: normal !important;
            font-size: 6.5pt !important;
        }
        .gz-grid-table thead th small { color: #000 !important; font-size: 6.5pt !important; }

        /* Cell content: keep the letter codes (N / ½ / CN / P / X), no color */
        .att-cell {
            min-width: 0 !important;
            color: #000 !important;
        }
        .att-cell a {
            color: #000 !important;
            text-decoration: none !important;
            font-weight: 600 !important;
        }
        .att-cell.att-absent a { font-weight: 800 !important; }
        .att-cell small { color: #000 !important; font-size: 6.5pt !important; }
    }
</style>
@endpush

@section('content')

@php
    $weekDays = app()->getLocale() === 'en'
        ? ['Sun','Mon','Tue','Wed','Thu','Fri','Sat']
        : ['CN','T2','T3','T4','T5','T6','T7'];
@endphp

<div class="gz-section-rule">
    <span class="gz-section-rule-text"><em>II</em> {{ __('Toàn Cảnh Trong Tháng') }}</span>
</div>

<div class="gz-card-head" style="margin-bottom: 1rem;">
    <div>
        <h2 class="gz-section-title mb-1">{{ __('Chấm công tháng') }} {{ $month }}/{{ $year }}</h2>
        <p class="gz-section-lede mb-0">
            {{ __('Bấm vào bất kỳ ô nào để chuyển sang trang chấm công ngày tương ứng.') }}
        </p>
    </div>
    <div class="d-flex gap-2 no-print">
        <form method="GET" class="d-flex gap-2">
            <select name="month" class="form-select form-select-sm">
                @for ($m=1; $m<=12; $m++)
                    <option value="{{ $m }}" @selected($m == $month)>{{ __('Tháng') }} {{ $m }}</option>
                @endfor
            </select>
            <input type="number" name="year" class="form-control form-control-sm" value="{{ $year }}" style="width:90px">
            <button class="btn btn-sm btn-outline-primary">{{ __('Xem') }}</button>
        </form>
        <button type="button" class="btn btn-sm btn-outline-primary"
                onclick="exportPdf('attendance-month', {year: {{ $year }}, month: {{ $month }}})">
            <i class="bi bi-file-earmark-pdf"></i> {{ __('Xuất PDF') }}
        </button>
        <a href="{{ route('attendance.index') }}" class="btn btn-sm btn-primary">
            <i class="bi bi-calendar-day"></i> {{ __('Chấm Công Hôm Nay') }}
        </a>
    </div>
</div>

<div class="gz-card">
    <div class="d-flex gap-3 flex-wrap mb-3 align-items-center no-print" style="font-size:0.85rem;">
        <span class="gz-label">{{ __('Chú thích:') }}</span>
        <span><span class="badge att-normal" style="padding:2px 8px; border-radius:0;">N</span> {{ __('Ngày thường') }}</span>
        <span><span class="badge att-half" style="padding:2px 8px; border-radius:0;">½</span> {{ __('Nửa ngày') }}</span>
        <span><span class="badge att-sunday" style="padding:2px 8px; border-radius:0;">CN</span> {{ __('Chủ nhật (×2 công)') }}</span>
        <span><span class="badge att-leave" style="padding:2px 8px; border-radius:0;">P</span> {{ __('Có phép') }}</span>
        <span><span class="badge att-absent" style="padding:2px 8px; border-radius:0;">X</span> {{ __('Không phép') }}</span>
        <span class="text-muted"><em>{{ __('Số nhỏ ở dưới = số ca tăng ca') }}</em></span>
    </div>

    <div class="table-responsive" style="max-height: 620px; overflow:auto;">
    <table class="gz-grid-table" style="min-width: 1200px;">
        <thead class="sticky-top">
            <tr>
                <th rowspan="2" style="min-width:200px; text-align:left; padding-left:0.6rem;">{{ __('Nhân viên') }}</th>
                <th colspan="{{ $end->day }}">{{ __('Ngày trong tháng') }} {{ $month }}/{{ $year }}</th>
            </tr>
            <tr>
                @for ($d=1; $d <= $end->day; $d++)
                    @php $date = $start->copy()->day($d); @endphp
                    <th class="{{ $date->isSunday() ? 'sunday-col' : '' }}" style="min-width:42px">
                        {{ $d }}<br><small style="color:var(--gz-muted);">{{ $weekDays[$date->dayOfWeek] }}</small>
                    </th>
                @endfor
            </tr>
        </thead>
        <tbody>
        @foreach ($employees as $emp)
        <tr>
            <td style="text-align:left; padding-left:0.6rem;">
                <strong>{{ $emp->employee_code }}</strong><br>
                <small style="color:var(--gz-muted);"><em>{{ $emp->full_name }}</em></small>
            </td>
            @for ($d=1; $d <= $end->day; $d++)
                @php
                    $date = $start->copy()->day($d);
                    $dateKey = $emp->id.'|'.$date->format('Y-m-d');
                    $att = $attendances->get($dateKey)?->first();
                    $ot = $overtimes->get($dateKey)?->first();
                    $cls = match ($att?->type) {
                        'normal'  => 'att-normal',
                        'half'    => 'att-half',
                        'sunday'  => 'att-sunday',
                        'leave'   => 'att-leave',
                        'absent'  => 'att-absent',
                        default   => '',
                    };
                    $letter = match ($att?->type) {
                        'normal' => 'N', 'half' => '½', 'sunday' => 'CN', 'leave' => 'P', 'absent' => 'X', default => '—',
                    };
                @endphp
                <td class="att-cell {{ $cls }}">
                    <a href="{{ route('attendance.index', ['date' => $date->format('Y-m-d')]) }}"
                       title="{{ __('Chấm ngày') }} {{ $date->format('d/m/Y') }}">
                        <strong>{{ $letter }}</strong>
                        @if ($ot && $ot->shifts > 0)
                            <br><small>+{{ $ot->shifts }}</small>
                        @endif
                    </a>
                </td>
            @endfor
        </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>

@endsection
