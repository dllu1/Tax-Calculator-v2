@extends('layouts.app')
@section('title', __('Bảng lương tháng').' '.$month.'/'.$year)

@php
    $fmt = fn($n) => $n ? number_format((float)$n, 0, ',', '.') : '';
    $allowColspan = max(1, count($allowance_names));
@endphp

@push('scripts')
<style>
    /* ===== Always show this as the print-ready full report ===== */
    .pdf-summary { font-family: 'IBM Plex Mono', Consolas, monospace; font-size: 9pt; color: #000; }
    .pdf-summary table { width: 100%; border-collapse: collapse; }
    .pdf-summary th, .pdf-summary td { border: 0.5pt solid #000; padding: 2pt 3pt; vertical-align: middle; }
    .pdf-summary thead th { background: #d9d9d9; font-weight: 700; text-align: center; }
    .pdf-summary tfoot td { background: #d9d9d9; font-weight: 700; border-top: 1.2pt solid #000; }
    .pdf-summary td.num { text-align: right; font-variant-numeric: tabular-nums; }
    .pdf-summary td.ctr { text-align: center; }
    .pdf-summary td.lbl { text-align: left; padding-left: 4pt; }

    .pdf-summary-toolbar { margin-bottom: 8pt; }
    @media print {
        @page { size: A3 landscape; margin: 6mm 6mm; }
        html, body { background: #fff !important; color: #000 !important; }
        .gz-nav, .gz-footer, .no-print, .alert { display: none !important; }
        main.container, .container { max-width: 100% !important; padding: 0 !important; }
        .pdf-summary { font-size: 7pt; }
        .pdf-summary th, .pdf-summary td { padding: 1pt 2pt; }
        .pdf-summary-toolbar { display: none !important; }
        .gz-section-rule { margin: 0 0 3pt 0 !important; }
        .gz-section-rule::before, .gz-section-rule::after { border-color: #000 !important; }
        .gz-section-rule-text, .gz-section-rule-text em { color: #000 !important; }
    }
</style>
@endpush

@section('content')

<div class="gz-section-rule">
    <span class="gz-section-rule-text"><em>V</em> {{ __('Bảng Lương Tháng') }} {{ $month }}/{{ $year }} — {{ __('Bản Đầy Đủ') }}</span>
</div>

<div class="pdf-summary-toolbar no-print d-flex justify-content-between align-items-center">
    <p class="gz-section-lede mb-0">
        {{ __('Định dạng quyết toán đầy đủ — bấm Ctrl+P để in/lưu PDF (A3 landscape).') }}
    </p>
    <button type="button" class="btn btn-sm btn-primary" onclick="window.print()">
        <i class="bi bi-printer"></i> {{ __('In ngay') }}
    </button>
</div>

<div class="pdf-summary">
<table>
    <thead>
        <tr>
            <th rowspan="2" style="width:30pt">{{ __('STT') }}</th>
            <th rowspan="2" style="width:50pt">{{ __('Mã NV') }}</th>
            <th rowspan="2" style="min-width:90pt">{{ __('Tên nhân viên') }}</th>
            <th colspan="2">{{ __('Lương ngày') }}</th>
            <th colspan="2">{{ __('Tiền ăn giữa ca') }}</th>
            <th colspan="2">{{ __('Tăng ca ngày thường') }}</th>
            <th colspan="2">{{ __('Tiền ăn TC thường') }}</th>
            <th colspan="2">{{ __('Tăng ca chủ nhật') }}</th>
            <th colspan="2">{{ __('Tiền ăn TC CN') }}</th>
            <th colspan="{{ $allowColspan }}">{{ __('PC không tính thuế') }}</th>
            <th rowspan="2">{{ __('Lương sản phẩm') }}</th>
            <th rowspan="2">{{ __('Thưởng Tết') }}</th>
            <th rowspan="2">{{ __('Lương phép năm') }}</th>
            <th rowspan="2">{{ __('Tổng số tiền thu nhập') }}</th>
            <th rowspan="2">{{ __('Số tiền tính thuế') }}</th>
            <th rowspan="2">{{ __('Lương BHXH') }}</th>
            <th rowspan="2">{{ __('BHXH Cty 21,5%') }}</th>
            <th rowspan="2">{{ __('BHXH CN 10,5%') }}</th>
            <th rowspan="2">{{ __('Tạm ứng') }}</th>
            <th colspan="2">{{ __('Giảm trừ gia cảnh') }}</th>
            <th rowspan="2">{{ __('Số tiền chịu thuế') }}</th>
            <th rowspan="2">{{ __('Tiền thuế TNCN') }}</th>
            <th rowspan="2">{{ __('Số tiền còn lại') }}</th>
        </tr>
        <tr>
            <th>{{ __('Số ngày') }}</th><th>{{ __('Số tiền') }}</th>
            <th>{{ __('Số ngày') }}</th><th>{{ __('Số tiền') }}</th>
            <th>{{ __('Số ca') }}</th><th>{{ __('Số tiền') }}</th>
            <th>{{ __('Số ca') }}</th><th>{{ __('Số tiền') }}</th>
            <th>{{ __('Số ca') }}</th><th>{{ __('Số tiền') }}</th>
            <th>{{ __('Số ca') }}</th><th>{{ __('Số tiền') }}</th>
            @forelse ($allowance_names as $name)
                <th>{{ $name }}</th>
            @empty
                <th>—</th>
            @endforelse
            <th>{{ __('Bản thân') }}</th><th>{{ __('NPT') }}</th>
        </tr>
    </thead>
    <tbody>
    @php $numFmt = fn($n) => $n ? rtrim(rtrim(number_format((float)$n, 1, ',', '.'), '0'), ',') : ''; @endphp
    @foreach ($rows as $i => $row)
        @php $emp = $row['employee']; $p = $row['payroll']; @endphp
        <tr>
            <td class="ctr">{{ $i + 1 }}</td>
            <td class="ctr"><strong>{{ $emp->employee_code }}</strong></td>
            <td class="lbl">{{ $emp->full_name }}</td>
            <td class="ctr">{{ $numFmt($row['weekday_work_days']) }}</td>
            <td class="num">{{ $fmt($row['weekday_day_wage']) }}</td>
            <td class="ctr">{{ $row['weekday_meal_days'] ?: '' }}</td>
            <td class="num">{{ $fmt($row['weekday_meal']) }}</td>
            <td class="ctr">{{ $row['ot_weekday_shifts'] ?: '' }}</td>
            <td class="num">{{ $fmt($row['ot_weekday_wage']) }}</td>
            <td class="ctr">{{ $row['ot_weekday_shifts'] ?: '' }}</td>
            <td class="num">{{ $fmt($row['ot_weekday_meal']) }}</td>
            <td class="ctr">{{ $numFmt($row['sunday_work_days']) }}</td>
            <td class="num">{{ $fmt($row['sunday_day_wage']) }}</td>
            <td class="ctr">{{ $row['sunday_meal_shifts'] ?: '' }}</td>
            <td class="num">{{ $fmt($row['sunday_meal']) }}</td>
            @forelse ($allowance_names as $name)
                <td class="num">{{ $fmt($row['allowances_by_name'][$name] ?? 0) }}</td>
            @empty
                <td class="num">—</td>
            @endforelse
            <td class="num">{{ $fmt($p->product_salary) }}</td>
            <td class="num">{{ $fmt($p->tet_bonus) }}</td>
            <td class="num">{{ $fmt($p->annual_leave_pay) }}</td>
            <td class="num"><strong>{{ $fmt($p->total_income) }}</strong></td>
            <td class="num">{{ $fmt($p->taxable_income) }}</td>
            <td class="num">{{ $fmt($emp->bhxh_salary) }}</td>
            <td class="num">{{ $fmt($row['employer_bhxh']) }}</td>
            <td class="num">{{ $fmt($p->bhxh_amount) }}</td>
            <td class="num">{{ $fmt($p->advance) }}</td>
            <td class="num">{{ $fmt($p->personal_deduction) }}</td>
            <td class="num">{{ $fmt($p->dependent_deduction) }}</td>
            <td class="num">{{ $fmt($p->assessable_income) }}</td>
            <td class="num">{{ $fmt($p->pit_amount) }}</td>
            <td class="num"><strong>{{ $fmt($p->net_salary) }}</strong></td>
        </tr>
    @endforeach
    @if (empty($rows))
        <tr><td colspan="35" class="ctr" style="padding: 1rem;"><em>{{ __('Chưa có dữ liệu lương') }}</em></td></tr>
    @endif
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3" class="lbl">{{ __('TỔNG CỘNG') }}</td>
            <td class="ctr">{{ $numFmt($totals['weekday_work_days']) }}</td>
            <td class="num">{{ $fmt($totals['weekday_day_wage']) }}</td>
            <td class="ctr">{{ $totals['weekday_meal_days'] ?: '' }}</td>
            <td class="num">{{ $fmt($totals['weekday_meal']) }}</td>
            <td class="ctr">{{ $totals['ot_weekday_shifts'] ?: '' }}</td>
            <td class="num">{{ $fmt($totals['ot_weekday_wage']) }}</td>
            <td class="ctr">{{ $totals['ot_weekday_shifts'] ?: '' }}</td>
            <td class="num">{{ $fmt($totals['ot_weekday_meal']) }}</td>
            <td class="ctr">{{ $numFmt($totals['sunday_work_days']) }}</td>
            <td class="num">{{ $fmt($totals['sunday_day_wage']) }}</td>
            <td class="ctr">{{ $totals['sunday_meal_shifts'] ?: '' }}</td>
            <td class="num">{{ $fmt($totals['sunday_meal']) }}</td>
            @forelse ($allowance_names as $name)
                <td class="num">{{ $fmt($totals_by_allowance[$name] ?? 0) }}</td>
            @empty
                <td class="num">—</td>
            @endforelse
            <td class="num">{{ $fmt($totals['product_salary']) }}</td>
            <td class="num">{{ $fmt($totals['tet_bonus']) }}</td>
            <td class="num">{{ $fmt($totals['annual_leave_pay']) }}</td>
            <td class="num">{{ $fmt($totals['total_income']) }}</td>
            <td class="num">{{ $fmt($totals['taxable_income']) }}</td>
            <td class="num">{{ $fmt($totals['bhxh_salary']) }}</td>
            <td class="num">{{ $fmt($totals['employer_bhxh']) }}</td>
            <td class="num">{{ $fmt($totals['bhxh_amount']) }}</td>
            <td class="num">{{ $fmt($totals['advance']) }}</td>
            <td class="num">{{ $fmt($totals['personal_deduction']) }}</td>
            <td class="num">{{ $fmt($totals['dependent_deduction']) }}</td>
            <td class="num">{{ $fmt($totals['assessable_income']) }}</td>
            <td class="num">{{ $fmt($totals['pit_amount']) }}</td>
            <td class="num">{{ $fmt($totals['net_salary']) }}</td>
        </tr>
    </tfoot>
</table>
</div>

@endsection
