@extends('layouts.app')
@section('title', __('Bảng lương').' '.$month.'/'.$year)

@php $fmt = fn($n) => number_format($n, 0, ',', '.'); @endphp

@push('scripts')
<style>
    /* ===== B&W Excel-style monthly payroll summary for print ===== */
    @media print {
        @page { size: A4 landscape; margin: 8mm 8mm; }

        html, body {
            background: #fff !important;
            color: #000 !important;
            font-family: 'IBM Plex Mono', Consolas, monospace !important;
            font-size: 9pt;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .gz-nav, .gz-footer, .no-print, .alert { display: none !important; }
        main.container, .container { max-width: 100% !important; padding: 0 !important; }

        /* Compact masthead */
        .gz-section-rule { margin: 0 0 3pt 0 !important; }
        .gz-section-rule::before, .gz-section-rule::after { border-color: #000 !important; }
        .gz-section-rule-text, .gz-section-rule-text em { color: #000 !important; }
        .gz-section-title { font-size: 11pt !important; color: #000 !important; margin: 0 0 3pt 0 !important; }
        .gz-section-lede { display: none !important; }
        .gz-card-head { margin: 0 0 4pt 0 !important; }

        /* Hide the 3 summary figure cards — the tfoot row already totals everything */
        .row.g-3.mb-3 { display: none !important; }

        .gz-card {
            background: transparent !important;
            border: none !important;
            padding: 0 !important;
            margin: 0 !important;
            box-shadow: none !important;
        }

        .payroll-scroll, .table-responsive {
            max-height: none !important;
            overflow: visible !important;
            border: none !important;
        }

        /* Hide the action-button column on print */
        .payroll-table-sticky thead th:last-child,
        .payroll-table-sticky tbody td:last-child,
        .payroll-table-sticky tfoot td:last-child { display: none !important; }

        .gz-table.payroll-table-sticky {
            font-family: 'IBM Plex Mono', Consolas, monospace !important;
            font-size: 9pt !important;
            border-collapse: collapse !important;
            width: 100% !important;
            page-break-inside: auto !important;
        }
        .payroll-table-sticky thead { display: table-header-group !important; }
        .payroll-table-sticky tr { page-break-inside: avoid !important; page-break-after: auto !important; }

        /* Override all sticky positioning + colored text */
        .payroll-table-sticky thead th,
        .payroll-table-sticky tfoot tr,
        .payroll-table-sticky tfoot td {
            position: static !important;
            box-shadow: none !important;
        }
        .payroll-table-sticky th,
        .payroll-table-sticky td {
            background: #fff !important;
            color: #000 !important;
            border: 0.5pt solid #000 !important;
            padding: 2pt 4pt !important;
            font-weight: normal !important;
            font-style: normal !important;
        }
        .payroll-table-sticky thead th {
            background: #d9d9d9 !important;
            font-weight: 700 !important;
            text-align: center !important;
            border: 0.6pt solid #000 !important;
        }
        .payroll-table-sticky tfoot td {
            background: #d9d9d9 !important;
            font-weight: 700 !important;
            border-top: 1.5pt solid #000 !important;
        }
        /* Strip color-class accents so the print stays true B&W */
        .payroll-table-sticky .text-danger,
        .payroll-table-sticky .text-success { color: #000 !important; }
        .payroll-table-sticky .fw-bold { font-weight: 700 !important; }

        .payroll-table-sticky .money { text-align: right !important; font-variant-numeric: tabular-nums; }
        .payroll-table-sticky .num { text-align: center !important; }
    }
</style>
@endpush

@section('content')

<div class="gz-section-rule">
    <span class="gz-section-rule-text"><em>I</em> {{ __('Bản Tổng Kê Tháng') }} {{ $month }}/{{ $year }}</span>
</div>

<div class="gz-card-head" style="margin-bottom: 1rem;">
    <div>
        <h2 class="gz-section-title mb-1">{{ __('Bảng lương toàn công ty') }}</h2>
        <p class="gz-section-lede mb-0">
            {{ __('Số liệu được tính lại tự động mỗi lần truy cập theo công thức hiện hành trong mục') }}
            <a href="{{ route('settings.index') }}">{{ __('Cấu Hình') }}</a>.
        </p>
    </div>
    <div class="d-flex gap-2 align-items-end no-print">
        <form method="GET" class="d-flex gap-2 align-items-end">
            <select name="month" class="form-select form-select-sm">
                @for ($m=1; $m<=12; $m++)
                    <option value="{{ $m }}" @selected($m == $month)>{{ __('Tháng') }} {{ $m }}</option>
                @endfor
            </select>
            <input type="number" name="year" class="form-control form-control-sm" value="{{ $year }}" style="width:90px">
            <button class="btn btn-sm btn-outline-primary">{{ __('Tính Lại') }}</button>
        </form>
        <button type="button" class="btn btn-sm btn-primary" onclick="window.print()">
            <i class="bi bi-file-earmark-pdf"></i> {{ __('Xuất PDF') }}
        </button>
    </div>
</div>

{{-- Tổng quan: 3 figure ngang --}}
<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="gz-card gz-card-tight">
            <div class="gz-label">{{ __('Tổng thực nhận') }}</div>
            <div class="gz-figure-sm">{{ $fmt($payrolls->sum('total_income')) }}
                <span class="gz-figure-unit">₫</span></div>
            <div class="gz-figure-caption">{{ $payrolls->count() }} {{ __('nhân viên') }} · {{ __('trước trừ') }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="gz-card gz-card-tight">
            <div class="gz-label">{{ __('Tổng thuế TNCN') }}</div>
            <div class="gz-figure-sm gz-figure-accent">{{ $fmt($payrolls->sum('pit_amount')) }}
                <span class="gz-figure-unit">₫</span></div>
            <div class="gz-figure-caption">{{ __('Lũy tiến năm bậc') }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="gz-card gz-card-tight">
            <div class="gz-label">{{ __('Tổng lương còn lại') }}</div>
            <div class="gz-figure-sm gz-figure-success">{{ $fmt($payrolls->sum('net_salary')) }}
                <span class="gz-figure-unit">₫</span></div>
            <div class="gz-figure-caption">{{ __('Sau khấu trừ & tạm ứng') }}</div>
        </div>
    </div>
</div>

<div class="gz-card">
    <div class="table-responsive payroll-scroll" style="max-height: 65vh; overflow-y: auto;">
    <table class="gz-table payroll-table-sticky">
        <thead>
            <tr>
                <th style="width:80px">{{ __('Mã NV') }}</th>
                <th>{{ __('Họ tên') }}</th>
                <th class="num" style="width:60px">{{ __('Thường') }}</th>
                <th class="num" style="width:50px">{{ __('CN') }}</th>
                <th class="num" style="width:50px">{{ __('TC') }}</th>
                <th class="money">{{ __('Tổng thực nhận') }}</th>
                <th class="money">{{ __('BHXH') }}</th>
                <th class="money">{{ __('Thuế TNCN') }}</th>
                <th class="money">{{ __('Tạm ứng') }}</th>
                <th class="money">{{ __('Còn lại') }}</th>
                <th style="width:42px"></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($payrolls as $p)
            <tr>
                <td><strong>{{ $p->employee->employee_code }}</strong></td>
                <td>{{ $p->employee->full_name }}</td>
                <td class="num">{{ $p->normal_days }}</td>
                <td class="num">{{ $p->sunday_days }}</td>
                <td class="num">{{ $p->overtime_shifts }}</td>
                <td class="money">{{ $fmt($p->total_income) }}</td>
                <td class="money">{{ $fmt($p->bhxh_amount) }}</td>
                <td class="money text-danger">{{ $fmt($p->pit_amount) }}</td>
                <td class="money">{{ $fmt($p->advance) }}</td>
                <td class="money fw-bold text-success">{{ $fmt($p->net_salary) }}</td>
                <td>
                    <div class="gz-actions">
                        <a href="{{ route('payroll.show', [$p->employee_id, $year, $month]) }}"
                           class="btn btn-sm btn-outline-info" title="{{ __('Xem phiếu lương') }}">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="{{ route('payroll.show', [$p->employee_id, $year, $month]) }}?print=1"
                           target="_blank"
                           class="btn btn-sm btn-outline-primary" title="{{ __('Xuất PDF') }}">
                            <i class="bi bi-file-earmark-pdf"></i>
                        </a>
                    </div>
                </td>
            </tr>
            @endforeach
            @if ($payrolls->isEmpty())
            <tr><td colspan="11" class="text-center" style="color:var(--gz-muted); padding:2rem;">
                <em>{{ __('Chưa có nhân viên để tính lương') }}</em>
            </td></tr>
            @endif
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="text-end">{{ __('TỔNG CỘNG') }}</td>
                <td class="money">{{ $fmt($payrolls->sum('total_income')) }}</td>
                <td class="money">{{ $fmt($payrolls->sum('bhxh_amount')) }}</td>
                <td class="money text-danger">{{ $fmt($payrolls->sum('pit_amount')) }}</td>
                <td class="money">{{ $fmt($payrolls->sum('advance')) }}</td>
                <td class="money text-success">{{ $fmt($payrolls->sum('net_salary')) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
    </div>
</div>

@endsection
