@extends('layouts.app')
@section('title', __('Phiếu lương').' '.$employee->full_name)

@push('scripts')
<style>
    @media print {
        @page { size: A4; margin: 14mm 16mm; }
        .gz-masthead, .gz-footer { display: none !important; }
    }
</style>
@endpush

@php
    $fmt = fn($n) => number_format($n, 0, ',', '.');
    $keepRatio = $payroll->total_income > 0
        ? round($payroll->net_salary / $payroll->total_income * 100, 1)
        : 0;
    $lossRatio = round(100 - $keepRatio, 1);
    $effectiveRate = $payroll->taxable_income > 0
        ? round($payroll->pit_amount / $payroll->taxable_income * 100, 2)
        : 0;
@endphp

@section('content')

<div class="no-print">

{{-- ===================== HEADER ===================== --}}
<div class="gz-section-rule">
    <span class="gz-section-rule-text"><em>I</em> {{ __('Thông Số Đầu Vào') }}</span>
</div>

<div class="gz-card-head" style="margin-bottom: 1rem;">
    <div>
        <h2 class="gz-section-title mb-1">
            {{ __('Phiếu lương: ') }}{{ $employee->full_name }}
            <span class="badge solid">{{ $employee->employee_code }}</span>
        </h2>
        <p class="gz-section-lede mb-0">
            {{ $employee->position }} · {{ $employee->department }} ·
            {{ __('Người phụ thuộc:') }} <strong>{{ $employee->dependents }}</strong> ·
            {{ __('Tháng') }} <strong>{{ $month }}/{{ $year }}</strong>
        </p>
    </div>
    <div class="d-flex gap-2 align-items-end no-print">
        <select class="form-select form-select-sm" id="month-nav"
                data-base="{{ route('payroll.show', [$employee->id, $year, 0]) }}">
            @for ($m=1; $m<=12; $m++)
                <option value="{{ $m }}" @selected($m == $month)>{{ __('Tháng') }} {{ $m }}/{{ $year }}</option>
            @endfor
        </select>
        <button type="button" class="btn btn-primary btn-sm"
                onclick="exportPdf('payslip', {year: {{ $year }}, month: {{ $month }}, employee: {{ $employee->id }}})">
            <i class="bi bi-file-earmark-pdf"></i> {{ __('Xuất PDF') }}
        </button>
    </div>
</div>

{{-- ===================== KẾT QUẢ — FIGURE LỚN ===================== --}}
<div class="gz-section-rule">
    <span class="gz-section-rule-text"><em>II</em> {{ __('Bản Tổng Kê Kỳ Lương') }}</span>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="gz-card">
            <div class="gz-label">{{ __('Thực nhận (Net)') }}</div>
            <div class="gz-figure gz-figure-success">
                {{ $fmt($payroll->net_salary) }}<span class="gz-figure-unit">{{ __('₫/tháng') }}</span>
            </div>
            <div class="gz-figure-caption">
                ≈ {{ $fmt($payroll->net_salary * 12) }} {{ __('₫/năm') }} · {{ __('Tỷ lệ giữ lại') }} {{ $keepRatio }}%
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="gz-card">
            <div class="gz-label">{{ __('Thuế TNCN phải nộp') }}</div>
            <div class="gz-figure gz-figure-accent">
                {{ $fmt($payroll->pit_amount) }}<span class="gz-figure-unit">{{ __('₫/tháng') }}</span>
            </div>
            <div class="gz-figure-caption">
                {{ __('Thuế suất hiệu quả') }} {{ $effectiveRate }}% ·
                {{ __('Biên') }} {{ (int) round(($payroll->detail['pit_rate'] ?? 0) * 100) }}%
            </div>
        </div>
    </div>
</div>

{{-- 4 ô tổng quan ngang --}}
<div class="row g-0 gz-card gz-card-flush">
    @php
        $cells = [
            [__('Tổng thực nhận'), $fmt($payroll->total_income)],
            [__('BHXH').' (10,5%)', $fmt($payroll->bhxh_amount)],
            [__('Giảm trừ gia cảnh'), $fmt($payroll->personal_deduction + $payroll->dependent_deduction)],
            [__('TN tính thuế'), $fmt($payroll->assessable_income)],
        ];
    @endphp
    @foreach ($cells as $c)
        <div class="col-md-3 gz-grid-cell">
            <div class="gz-label">{{ $c[0] }}</div>
            <div class="gz-figure-sm">{{ $c[1] }}</div>
        </div>
    @endforeach
</div>

{{-- ===================== DÒNG TIỀN THÁNG ===================== --}}
<div class="gz-section-rule">
    <span class="gz-section-rule-text"><em>III</em> {{ __('Trước & Sau Khấu Trừ — Dòng Tiền Tháng') }}</span>
</div>

<div class="gz-card gz-card-flush gz-cashflow">
    <div class="row g-0">
        @php
            $flow = [
                ['01', __('Tổng thực nhận'), $fmt($payroll->total_income), null],
                ['02', __('Trừ BHXH/BHYT/BHTN (10,5%)'), $fmt($payroll->total_income - $payroll->bhxh_amount), '−'.$fmt($payroll->bhxh_amount)],
                ['03', __('Trừ giảm trừ gia cảnh'), $fmt($payroll->assessable_income), '−'.$fmt($payroll->personal_deduction + $payroll->dependent_deduction)],
                ['04', __('Thuế TNCN lũy tiến'), $fmt($payroll->pit_amount), '−'.$fmt($payroll->pit_amount)],
                ['05', __('Thực nhận'), $fmt($payroll->net_salary), null, true],
            ];
        @endphp
        @foreach ($flow as $f)
        <div class="col gz-cashflow-cell {{ ($f[4] ?? false) ? 'highlight' : '' }}">
            <div class="gz-cashflow-step">{{ $f[0] }}</div>
            <div class="gz-cashflow-label">{{ $f[1] }}</div>
            <div class="gz-figure-sm">{{ $f[2] }}</div>
            @if ($f[3])
                <div class="gz-cashflow-delta">{{ $f[3] }}</div>
            @endif
        </div>
        @endforeach
    </div>
</div>
<div class="mb-3" style="font-size:0.85rem; color:var(--gz-muted); font-style:italic;">
    {{ __('Tỷ lệ giữ lại') }}: <strong style="color:var(--gz-ink);">{{ $keepRatio }}%</strong> ·
    {{ __('Mất do thuế & BH:') }} <strong style="color:var(--gz-accent);">{{ $lossRatio }}%</strong>
</div>

{{-- ===================== CHI TIẾT 2 CỘT ===================== --}}
<div class="gz-section-rule">
    <span class="gz-section-rule-text"><em>IV</em> {{ __('Chi Tiết Tính Toán') }}</span>
</div>

<div class="row g-3">
    {{-- Cột trái: lương --}}
    <div class="col-md-7">
        <div class="gz-card">
            <div class="gz-card-head" style="margin-bottom: 0.4rem;">
                <h4 class="mb-0">{{ __('Cấu thành tiền lương') }}</h4>
                <span class="gz-label">{{ __('Tháng') }} {{ $month }}/{{ $year }}</span>
            </div>
            <table class="gz-ledger">
                <tr class="section"><td colspan="2">{{ __('Công & tăng ca') }}</td></tr>
                <tr><td>{{ __('Ngày công thường') }}</td><td>{{ $payroll->normal_days }} {{ __('ngày') }}</td></tr>
                <tr><td>{{ __('Ngày chủ nhật') }} <em>(×{{ $payroll->detail['config']['sunday_multiplier'] ?? 2 }})</em></td><td>{{ $payroll->sunday_days }} {{ __('ngày') }}</td></tr>
                <tr><td>{{ __('Số ca tăng ca') }} <em>(3h = ½ {{ __('ngày') }})</em></td><td>{{ $payroll->overtime_shifts }} {{ __('ca') }}</td></tr>
                @if ($payroll->absent_days > 0)
                <tr class="minus"><td>{{ __('Ngày nghỉ không phép') }}</td><td>{{ $payroll->absent_days }}</td></tr>
                @endif

                <tr class="section"><td colspan="2">{{ __('Các khoản thu nhập') }}</td></tr>
                <tr><td>{{ __('Lương ngày công') }}</td><td>{{ $fmt($payroll->day_wage) }}</td></tr>
                <tr><td>{{ __('Lương tăng ca') }}</td><td>{{ $fmt($payroll->overtime_wage) }}</td></tr>
                <tr><td>{{ __('Tiền ăn giữa ca') }}</td><td>{{ $fmt($payroll->meal_shift) }}</td></tr>
                <tr><td>{{ __('Tiền ăn tăng ca') }}</td><td>{{ $fmt($payroll->meal_overtime) }}</td></tr>
                <tr><td>{{ __('Lương sản phẩm') }}</td><td>{{ $fmt($payroll->product_salary) }}</td></tr>
                <tr><td>{{ __('Chuyên cần') }}</td><td>{{ $fmt($payroll->diligence) }}</td></tr>
                <tr><td>{{ __('Lương nửa ngày') }} <small class="text-muted">({{ $payroll->half_days ?? 0 }} {{ __('nửa ngày') }} × ½ {{ __('chuyên cần') }})</small></td><td>{{ $fmt($payroll->half_day_amount ?? 0) }}</td></tr>
                <tr><td>{{ __('Thưởng Tết') }}</td><td>{{ $fmt($payroll->tet_bonus ?? 0) }}</td></tr>
                <tr><td>{{ __('Lương phép năm') }}</td><td>{{ $fmt($payroll->annual_leave_pay ?? 0) }}</td></tr>
                <tr><td>{{ __('Phụ cấp chịu thuế') }}</td><td>{{ $fmt($payroll->taxable_allowances) }}</td></tr>
                <tr><td>{{ __('Phụ cấp không chịu thuế') }}</td><td>{{ $fmt($payroll->non_taxable_allowances) }}</td></tr>

                <tr class="total"><td>{{ __('Tổng thực nhận') }}</td><td>{{ $fmt($payroll->total_income) }}</td></tr>

                <tr class="section"><td colspan="2">{{ __('Khấu trừ') }}</td></tr>
                <tr class="minus"><td>{{ __('BHXH') }} (10,5% × {{ $fmt($employee->bhxh_salary) }})</td><td>−{{ $fmt($payroll->bhxh_amount) }}</td></tr>
                <tr class="minus"><td>{{ __('Thuế TNCN') }}</td><td>−{{ $fmt($payroll->pit_amount) }}</td></tr>
                <tr class="minus"><td>{{ __('Tạm ứng') }}</td><td>−{{ $fmt($payroll->advance) }}</td></tr>

                <tr class="total plus"><td>{{ __('Tiền lương còn lại') }}</td><td>{{ $fmt($payroll->net_salary) }}</td></tr>
            </table>
        </div>
    </div>

    {{-- Cột phải: thuế --}}
    <div class="col-md-5">
        <div class="gz-card">
            <div class="gz-card-head" style="margin-bottom: 0.4rem;">
                <h4 class="mb-0">{{ __('Tính thuế TNCN') }}</h4>
                <span class="gz-label">{{ __('5 bậc lũy tiến') }}</span>
            </div>
            <table class="gz-ledger">
                <tr><td>{{ __('Lương ngày công') }}</td><td>{{ $fmt($payroll->day_wage) }}</td></tr>
                <tr><td>{{ __('+ Chuyên cần') }}</td><td>{{ $fmt($payroll->diligence) }}</td></tr>
                <tr><td>{{ __('+ Lương sản phẩm') }}</td><td>{{ $fmt($payroll->product_salary) }}</td></tr>
                <tr><td>{{ __('+ Phụ cấp chịu thuế') }}</td><td>{{ $fmt($payroll->taxable_allowances) }}</td></tr>
                <tr><td>{{ __('+ Thưởng Tết') }}</td><td>{{ $fmt($payroll->tet_bonus ?? 0) }}</td></tr>
                <tr><td>{{ __('+ Lương phép năm') }}</td><td>{{ $fmt($payroll->annual_leave_pay ?? 0) }}</td></tr>
                <tr class="total"><td>{{ __('= Thu nhập tính thuế') }}</td><td>{{ $fmt($payroll->taxable_income) }}</td></tr>

                <tr class="plus"><td>{{ __('− Giảm trừ bản thân') }}</td><td>{{ $fmt($payroll->personal_deduction) }}</td></tr>
                <tr class="plus"><td>{{ __('− Giảm trừ NPT') }} ({{ $employee->dependents }} {{ __('người)') }}</td><td>{{ $fmt($payroll->dependent_deduction) }}</td></tr>
                <tr class="plus"><td>{{ __('− BHXH 10,5%') }}</td><td>{{ $fmt($payroll->bhxh_amount) }}</td></tr>

                <tr class="total"><td>{{ __('= Thu nhập chịu thuế') }}</td><td>{{ $fmt($payroll->assessable_income) }}</td></tr>
                <tr class="total minus"><td>{{ __('Thuế TNCN phải nộp') }}</td><td>{{ $fmt($payroll->pit_amount) }}</td></tr>
            </table>

            @if (!empty($payroll->detail['pit_rate']))
                <p class="mt-3 mb-0" style="font-size:0.85rem; color:var(--gz-muted); font-style:italic;">
                    {{ __('Áp bậc thuế') }} <strong style="color:var(--gz-ink);">{{ (int) round($payroll->detail['pit_rate'] * 100) }}%</strong> ·
                    {{ __('Khấu trừ ') }}{{ $fmt($payroll->detail['pit_deduction']) }} ₫
                </p>
            @endif
        </div>
    </div>
</div>

{{-- ===================== QUẢN LÝ DỮ LIỆU THÁNG ===================== --}}
<div class="gz-section-rule">
    <span class="gz-section-rule-text"><em>V</em> {{ __('Điều Chỉnh Dữ Liệu Tháng') }}</span>
</div>

<ul class="nav nav-tabs" role="tablist">
    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-product">
        {{ __('Lương sản phẩm') }}</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-allowance">
        {{ __('Phụ cấp') }}</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-advance">
        {{ __('Tạm ứng') }}</button></li>
</ul>

<div class="tab-content gz-tab-body">
    {{-- Tab Lương SP --}}
    <div class="tab-pane fade show active" id="tab-product">
        <form method="POST" action="{{ route('product-salary.store', $employee) }}" class="row g-2 align-items-end"
              data-ajax="true" data-soft-reload="true">
            @csrf
            <input type="hidden" name="year" value="{{ $year }}">
            <input type="hidden" name="month" value="{{ $month }}">
            <div class="col-md-4">
                <label class="form-label">{{ __('Số tiền lương SP') }}</label>
                <input type="number" step="1000" name="amount" class="form-control"
                       value="{{ $productSalary?->amount ?? 0 }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">{{ __('Ghi chú') }}</label>
                <input name="note" class="form-control" value="{{ $productSalary?->note }}">
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100"><i class="bi bi-save"></i> {{ __('Lưu') }}</button>
            </div>
        </form>
    </div>

    {{-- Tab Phụ cấp --}}
    <div class="tab-pane fade" id="tab-allowance">
        <form method="POST" action="{{ route('allowance.store', $employee) }}" class="row g-2 align-items-end mb-3"
              data-ajax="true" data-soft-reload="true" data-reset-after="true">
            @csrf
            <input type="hidden" name="year" value="{{ $year }}">
            <input type="hidden" name="month" value="{{ $month }}">
            <div class="col-md-3">
                <label class="form-label">{{ __('Tên phụ cấp') }}</label>
                <input name="name" class="form-control" placeholder="{{ __('VD: Xăng xe') }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">{{ __('Loại') }}</label>
                <select name="type" class="form-select" required>
                    <option value="taxable">{{ __('Có thuế (cộng vào TNTT)') }}</option>
                    <option value="non_taxable">{{ __('Không thuế') }}</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">{{ __('Số tiền') }}</label>
                <input type="number" step="1000" name="amount" class="form-control" required>
            </div>
            <div class="col-md-3">
                <button class="btn btn-primary w-100"><i class="bi bi-plus-lg"></i> {{ __('Thêm') }}</button>
            </div>
        </form>
        <table class="gz-table">
            <thead><tr><th>{{ __('Tên') }}</th><th>{{ __('Loại') }}</th><th class="money">{{ __('Số tiền') }}</th><th></th></tr></thead>
            <tbody>
            @forelse ($allowances as $a)
                <tr>
                    <td>{{ $a->name }}</td>
                    <td>
                        @if ($a->type === 'taxable')
                            <span class="badge bg-warning">{{ __('Có thuế') }}</span>
                        @else
                            <span class="badge bg-success">{{ __('Không thuế') }}</span>
                        @endif
                    </td>
                    <td class="money">{{ $fmt($a->amount) }}</td>
                    <td class="text-end">
                        <div class="gz-actions">
                            <button type="button" class="btn btn-sm btn-outline-danger" title="{{ __('Xóa phụ cấp') }}"
                                    data-ajax-delete="{{ route('allowance.destroy', $a) }}"
                                    data-soft-reload="true">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center" style="color:var(--gz-muted);"><em>{{ __('Chưa có phụ cấp') }}</em></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- Tab Tạm ứng --}}
    <div class="tab-pane fade" id="tab-advance">
        <form method="POST" action="{{ route('advance.store', $employee) }}" class="row g-2 align-items-end mb-3"
              data-ajax="true" data-soft-reload="true" data-reset-after="true">
            @csrf
            <input type="hidden" name="year" value="{{ $year }}">
            <input type="hidden" name="month" value="{{ $month }}">
            <div class="col-md-3">
                <label class="form-label">{{ __('Số tiền tạm ứng') }}</label>
                <input type="number" step="1000" name="amount" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">{{ __('Ngày tạm ứng') }}</label>
                <input type="date" name="advance_date" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">{{ __('Ghi chú') }}</label>
                <input name="note" class="form-control">
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100"><i class="bi bi-plus-lg"></i> {{ __('Thêm') }}</button>
            </div>
        </form>
        <table class="gz-table">
            <thead><tr><th>{{ __('Ngày') }}</th><th class="money">{{ __('Số tiền') }}</th><th>{{ __('Ghi chú') }}</th><th></th></tr></thead>
            <tbody>
            @forelse ($advances as $a)
                <tr>
                    <td>{{ optional($a->advance_date)->format('d/m/Y') }}</td>
                    <td class="money">{{ $fmt($a->amount) }}</td>
                    <td><em>{{ $a->note }}</em></td>
                    <td class="text-end">
                        <div class="gz-actions">
                            <button type="button" class="btn btn-sm btn-outline-danger" title="{{ __('Xóa tạm ứng') }}"
                                    data-ajax-delete="{{ route('advance.destroy', $a) }}"
                                    data-soft-reload="true">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center" style="color:var(--gz-muted);"><em>{{ __('Chưa có tạm ứng') }}</em></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

</div> {{-- /.no-print --}}

{{-- ============================================================ --}}
{{-- ===== PHIẾU LƯƠNG IN — COMPACT 1 TRANG A4 (PRINT ONLY) ===== --}}
{{-- ============================================================ --}}
@php
    $employerBhxhRate = 0.215; // BHXH+BHYT+BHTN chủ SD đóng (Vietnamese standard)
    $employerBhxhAmount = round($employerBhxhRate * (float) $employee->bhxh_salary, 0);
    $employeeBhxhRate = $payroll->bhxh_amount > 0 && $employee->bhxh_salary > 0
        ? $payroll->bhxh_amount / $employee->bhxh_salary
        : 0.105;
    $mealDays = $payroll->normal_days + $payroll->sunday_days + $payroll->half_days;
    $mealPerDay = (int) ($payroll->detail['config']['meal_per_day'] ?? 30000);
    $mealPerOt = (int) ($payroll->detail['config']['meal_per_ot_shift'] ?? 30000);
    $otMultiplier = (float) ($payroll->detail['config']['overtime_multiplier'] ?? 0.5);
    $otRate = round(($payroll->detail['daily_rate'] ?? 0) * $otMultiplier, 0);
@endphp

<div class="print-only payslip-print">
    <div class="payslip-header">
        <div class="payslip-code-box">{{ $employee->employee_code }}</div>
        <h1 class="payslip-title">PHIẾU TÍNH LƯƠNG</h1>
        <div class="payslip-period">THÁNG {{ str_pad($month, 2, '0', STR_PAD_LEFT) }} NĂM {{ $year }}</div>
    </div>

    <div class="payslip-emp-row">
        <div class="payslip-emp-name">{{ mb_strtoupper($employee->full_name, 'UTF-8') }}</div>
        <div class="payslip-emp-salary">
            <span class="lbl"><em>Lương tháng</em></span>
            <span class="val">{{ $fmt($employee->basic_salary) }}</span>
        </div>
    </div>

    <table class="payslip-table">
        <thead>
            <tr>
                <th class="col-label">Danh mục</th>
                <th class="col-days">Số ngày</th>
                <th class="col-rate">Lương ngày</th>
                <th class="col-amount">Số tiền</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Lương ngày</td>
                <td class="num">{{ number_format($payroll->detail['total_work_days'] ?? 0, 2, ',', '.') }}</td>
                <td class="num">{{ $fmt(round($payroll->detail['daily_rate'] ?? 0)) }}</td>
                <td class="num">{{ $fmt($payroll->day_wage) }}</td>
            </tr>
            @if ($payroll->overtime_shifts > 0)
            <tr>
                <td>Tăng ca</td>
                <td class="num">{{ number_format($payroll->overtime_shifts, 2, ',', '.') }}</td>
                <td class="num">{{ $fmt($otRate) }}</td>
                <td class="num">{{ $fmt($payroll->overtime_wage) }}</td>
            </tr>
            @endif
            @if ($payroll->meal_shift > 0)
            <tr>
                <td>Tiền ăn giữa ca</td>
                <td class="num">{{ $mealDays }}</td>
                <td class="num">{{ $fmt($mealPerDay) }}</td>
                <td class="num">{{ $fmt($payroll->meal_shift) }}</td>
            </tr>
            @endif
            @if ($payroll->meal_overtime > 0)
            <tr>
                <td>Tiền ăn tăng ca</td>
                <td class="num">{{ $payroll->overtime_shifts }}</td>
                <td class="num">{{ $fmt($mealPerOt) }}</td>
                <td class="num">{{ $fmt($payroll->meal_overtime) }}</td>
            </tr>
            @endif
            @if ($payroll->product_salary > 0)
            <tr>
                <td>Lương sản phẩm</td>
                <td></td>
                <td></td>
                <td class="num">{{ $fmt($payroll->product_salary) }}</td>
            </tr>
            @endif
            @foreach ($allowances as $a)
            <tr>
                <td>{{ $a->name }}</td>
                <td></td>
                <td></td>
                <td class="num">{{ $fmt($a->amount) }}</td>
            </tr>
            @endforeach
            @if ($payroll->diligence > 0)
            <tr>
                <td>Chuyên cần</td>
                <td></td>
                <td></td>
                <td class="num">{{ $fmt($payroll->diligence) }}</td>
            </tr>
            @endif
            @if (($payroll->half_day_amount ?? 0) > 0)
            <tr>
                <td>Lương nửa ngày ({{ $payroll->half_days }} nửa ngày)</td>
                <td></td>
                <td></td>
                <td class="num">{{ $fmt($payroll->half_day_amount) }}</td>
            </tr>
            @endif
            @if (($payroll->tet_bonus ?? 0) > 0)
            <tr>
                <td>Thưởng Tết</td>
                <td></td>
                <td></td>
                <td class="num">{{ $fmt($payroll->tet_bonus) }}</td>
            </tr>
            @endif
            @if (($payroll->annual_leave_pay ?? 0) > 0)
            <tr>
                <td>Lương phép năm</td>
                <td></td>
                <td></td>
                <td class="num">{{ $fmt($payroll->annual_leave_pay) }}</td>
            </tr>
            @endif
            <tr class="strikethrough">
                <td>BHXH, BHYT (21,5%) <em>— chủ SD đóng</em></td>
                <td class="num">{{ number_format($employerBhxhRate, 3, ',', '.') }}</td>
                <td class="num">{{ $fmt($employee->bhxh_salary) }}</td>
                <td class="num">{{ $fmt($employerBhxhAmount) }}</td>
            </tr>
            <tr class="total-row">
                <td colspan="3" class="lbl">Tổng tiền lương</td>
                <td class="num">{{ $fmt($payroll->total_income) }}</td>
            </tr>
        </tbody>
    </table>

    <table class="payslip-table payslip-deduct">
        <tbody>
            <tr>
                <td rowspan="{{ 1 + ($payroll->pit_amount > 0 ? 1 : 0) + ($payroll->advance > 0 ? 1 : 0) }}" class="rowhead">Trừ tiền</td>
                <td>BHXH (10,5%)</td>
                <td class="num">{{ number_format($employeeBhxhRate, 3, ',', '.') }}</td>
                <td class="num">{{ $fmt($employee->bhxh_salary) }}</td>
                <td class="num">{{ $fmt($payroll->bhxh_amount) }}</td>
            </tr>
            @if ($payroll->pit_amount > 0)
            <tr>
                <td>Thuế TNCN</td>
                <td></td>
                <td></td>
                <td class="num">{{ $fmt($payroll->pit_amount) }}</td>
            </tr>
            @endif
            @if ($payroll->advance > 0)
            <tr>
                <td>Tạm ứng</td>
                <td></td>
                <td></td>
                <td class="num">{{ $fmt($payroll->advance) }}</td>
            </tr>
            @endif
            <tr class="net-row">
                <td colspan="4" class="lbl">Thực lãnh</td>
                <td class="num">{{ $fmt($payroll->net_salary) }}</td>
            </tr>
        </tbody>
    </table>
</div>

@push('scripts')
<script>
    document.getElementById('month-nav')?.addEventListener('change', function () {
        const base = this.dataset.base;
        location.href = base.replace(/\/0$/, '/' + this.value);
    });
    // Tự động mở dialog in nếu URL có ?print=1
    if (new URLSearchParams(location.search).get('print') === '1') {
        window.addEventListener('load', () => setTimeout(() => window.print(), 400));
    }
</script>
@endpush
@endsection
