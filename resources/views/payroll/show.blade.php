@extends('layouts.app')
@section('title', 'Phiếu lương '.$employee->full_name)

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

{{-- ===================== HEADER ===================== --}}
<div class="gz-section-rule">
    <span class="gz-section-rule-text"><em>I</em> Thông Số Đầu Vào</span>
</div>

<div class="gz-card-head" style="margin-bottom: 1rem;">
    <div>
        <h2 class="gz-section-title mb-1">
            Phiếu lương: {{ $employee->full_name }}
            <span class="badge solid">{{ $employee->employee_code }}</span>
        </h2>
        <p class="gz-section-lede mb-0">
            {{ $employee->position }} · {{ $employee->department }} ·
            Người phụ thuộc: <strong>{{ $employee->dependents }}</strong> ·
            Tháng <strong>{{ $month }}/{{ $year }}</strong>
        </p>
    </div>
    <div class="d-flex gap-2 align-items-end no-print">
        <select class="form-select form-select-sm" id="month-nav"
                data-base="{{ route('payroll.show', [$employee->id, $year, 0]) }}">
            @for ($m=1; $m<=12; $m++)
                <option value="{{ $m }}" @selected($m == $month)>Tháng {{ $m }}/{{ $year }}</option>
            @endfor
        </select>
        <button type="button" class="btn btn-primary btn-sm" onclick="window.print()">
            <i class="bi bi-file-earmark-pdf"></i> Xuất PDF
        </button>
    </div>
</div>

{{-- ===================== KẾT QUẢ — FIGURE LỚN ===================== --}}
<div class="gz-section-rule">
    <span class="gz-section-rule-text"><em>II</em> Bản Tổng Kê Kỳ Lương</span>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="gz-card">
            <div class="gz-label">Thực nhận (Net)</div>
            <div class="gz-figure gz-figure-success">
                {{ $fmt($payroll->net_salary) }}<span class="gz-figure-unit">₫/tháng</span>
            </div>
            <div class="gz-figure-caption">
                ≈ {{ $fmt($payroll->net_salary * 12) }} ₫/năm · Tỷ lệ giữ lại {{ $keepRatio }}%
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="gz-card">
            <div class="gz-label">Thuế TNCN phải nộp</div>
            <div class="gz-figure gz-figure-accent">
                {{ $fmt($payroll->pit_amount) }}<span class="gz-figure-unit">₫/tháng</span>
            </div>
            <div class="gz-figure-caption">
                Thuế suất hiệu quả {{ $effectiveRate }}% ·
                Biên {{ (int) round(($payroll->detail['pit_rate'] ?? 0) * 100) }}%
            </div>
        </div>
    </div>
</div>

{{-- 4 ô tổng quan ngang --}}
<div class="row g-0 gz-card gz-card-flush">
    @php
        $cells = [
            ['Tổng thực nhận', $fmt($payroll->total_income)],
            ['BHXH (10,5%)', $fmt($payroll->bhxh_amount)],
            ['Giảm trừ gia cảnh', $fmt($payroll->personal_deduction + $payroll->dependent_deduction)],
            ['TN tính thuế', $fmt($payroll->assessable_income)],
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
    <span class="gz-section-rule-text"><em>III</em> Trước &amp; Sau Khấu Trừ — Dòng Tiền Tháng</span>
</div>

<div class="gz-card gz-card-flush gz-cashflow">
    <div class="row g-0">
        @php
            $flow = [
                ['01', 'Tổng thực nhận', $fmt($payroll->total_income), null],
                ['02', 'Trừ BHXH/BHYT/BHTN (10,5%)', $fmt($payroll->total_income - $payroll->bhxh_amount), '−'.$fmt($payroll->bhxh_amount)],
                ['03', 'Trừ giảm trừ gia cảnh', $fmt($payroll->assessable_income), '−'.$fmt($payroll->personal_deduction + $payroll->dependent_deduction)],
                ['04', 'Thuế TNCN lũy tiến', $fmt($payroll->pit_amount), '−'.$fmt($payroll->pit_amount)],
                ['05', 'Thực nhận', $fmt($payroll->net_salary), null, true],
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
    Tỷ lệ giữ lại: <strong style="color:var(--gz-ink);">{{ $keepRatio }}%</strong> ·
    Mất do thuế &amp; BH: <strong style="color:var(--gz-accent);">{{ $lossRatio }}%</strong>
</div>

{{-- ===================== CHI TIẾT 2 CỘT ===================== --}}
<div class="gz-section-rule">
    <span class="gz-section-rule-text"><em>IV</em> Chi Tiết Tính Toán</span>
</div>

<div class="row g-3">
    {{-- Cột trái: lương --}}
    <div class="col-md-7">
        <div class="gz-card">
            <div class="gz-card-head" style="margin-bottom: 0.4rem;">
                <h4 class="mb-0">Cấu thành tiền lương</h4>
                <span class="gz-label">Tháng {{ $month }}/{{ $year }}</span>
            </div>
            <table class="gz-ledger">
                <tr class="section"><td colspan="2">Công &amp; tăng ca</td></tr>
                <tr><td>Ngày công thường</td><td>{{ $payroll->normal_days }} ngày</td></tr>
                <tr><td>Ngày chủ nhật <em>(×{{ $payroll->detail['config']['sunday_multiplier'] ?? 2 }})</em></td><td>{{ $payroll->sunday_days }} ngày</td></tr>
                <tr><td>Số ca tăng ca <em>(3h = ½ ngày)</em></td><td>{{ $payroll->overtime_shifts }} ca</td></tr>
                @if ($payroll->absent_days > 0)
                <tr class="minus"><td>Ngày nghỉ không phép</td><td>{{ $payroll->absent_days }}</td></tr>
                @endif

                <tr class="section"><td colspan="2">Các khoản thu nhập</td></tr>
                <tr><td>Lương ngày công</td><td>{{ $fmt($payroll->day_wage) }}</td></tr>
                <tr><td>Lương tăng ca</td><td>{{ $fmt($payroll->overtime_wage) }}</td></tr>
                <tr><td>Tiền ăn giữa ca</td><td>{{ $fmt($payroll->meal_shift) }}</td></tr>
                <tr><td>Tiền ăn tăng ca</td><td>{{ $fmt($payroll->meal_overtime) }}</td></tr>
                <tr><td>Lương sản phẩm</td><td>{{ $fmt($payroll->product_salary) }}</td></tr>
                <tr><td>Chuyên cần</td><td>{{ $fmt($payroll->diligence) }}</td></tr>
                <tr><td>Phụ cấp chịu thuế</td><td>{{ $fmt($payroll->taxable_allowances) }}</td></tr>
                <tr><td>Phụ cấp không chịu thuế</td><td>{{ $fmt($payroll->non_taxable_allowances) }}</td></tr>

                <tr class="total"><td>Tổng thực nhận</td><td>{{ $fmt($payroll->total_income) }}</td></tr>

                <tr class="section"><td colspan="2">Khấu trừ</td></tr>
                <tr class="minus"><td>BHXH (10,5% × {{ $fmt($employee->bhxh_salary) }})</td><td>−{{ $fmt($payroll->bhxh_amount) }}</td></tr>
                <tr class="minus"><td>Thuế TNCN</td><td>−{{ $fmt($payroll->pit_amount) }}</td></tr>
                <tr class="minus"><td>Tạm ứng</td><td>−{{ $fmt($payroll->advance) }}</td></tr>

                <tr class="total plus"><td>Tiền lương còn lại</td><td>{{ $fmt($payroll->net_salary) }}</td></tr>
            </table>
        </div>
    </div>

    {{-- Cột phải: thuế --}}
    <div class="col-md-5">
        <div class="gz-card">
            <div class="gz-card-head" style="margin-bottom: 0.4rem;">
                <h4 class="mb-0">Tính thuế TNCN</h4>
                <span class="gz-label">5 bậc lũy tiến</span>
            </div>
            <table class="gz-ledger">
                <tr><td>Lương căn bản</td><td>{{ $fmt($employee->basic_salary) }}</td></tr>
                <tr><td>+ Lương sản phẩm</td><td>{{ $fmt($payroll->product_salary) }}</td></tr>
                <tr><td>+ Phụ cấp chịu thuế</td><td>{{ $fmt($payroll->taxable_allowances) }}</td></tr>
                <tr class="total"><td>= Thu nhập tính thuế</td><td>{{ $fmt($payroll->taxable_income) }}</td></tr>

                <tr class="plus"><td>− Giảm trừ bản thân</td><td>{{ $fmt($payroll->personal_deduction) }}</td></tr>
                <tr class="plus"><td>− Giảm trừ NPT ({{ $employee->dependents }} người)</td><td>{{ $fmt($payroll->dependent_deduction) }}</td></tr>
                <tr class="plus"><td>− BHXH 10,5%</td><td>{{ $fmt($payroll->bhxh_amount) }}</td></tr>

                <tr class="total"><td>= Thu nhập chịu thuế</td><td>{{ $fmt($payroll->assessable_income) }}</td></tr>
                <tr class="total minus"><td>Thuế TNCN phải nộp</td><td>{{ $fmt($payroll->pit_amount) }}</td></tr>
            </table>

            @if (!empty($payroll->detail['pit_rate']))
                <p class="mt-3 mb-0" style="font-size:0.85rem; color:var(--gz-muted); font-style:italic;">
                    Áp bậc thuế <strong style="color:var(--gz-ink);">{{ (int) round($payroll->detail['pit_rate'] * 100) }}%</strong> ·
                    Khấu trừ {{ $fmt($payroll->detail['pit_deduction']) }} ₫
                </p>
            @endif
        </div>
    </div>
</div>

{{-- ===================== QUẢN LÝ DỮ LIỆU THÁNG ===================== --}}
<div class="gz-section-rule">
    <span class="gz-section-rule-text"><em>V</em> Điều Chỉnh Dữ Liệu Tháng</span>
</div>

<ul class="nav nav-tabs" role="tablist">
    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-product">
        Lương sản phẩm</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-allowance">
        Phụ cấp</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-advance">
        Tạm ứng</button></li>
</ul>

<div class="tab-content gz-tab-body">
    {{-- Tab Lương SP --}}
    <div class="tab-pane fade show active" id="tab-product">
        <form method="POST" action="{{ route('product-salary.store', $employee) }}" class="row g-2 align-items-end">
            @csrf
            <input type="hidden" name="year" value="{{ $year }}">
            <input type="hidden" name="month" value="{{ $month }}">
            <div class="col-md-4">
                <label class="form-label">Số tiền lương SP</label>
                <input type="number" step="1000" name="amount" class="form-control"
                       value="{{ $productSalary?->amount ?? 0 }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Ghi chú</label>
                <input name="note" class="form-control" value="{{ $productSalary?->note }}">
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100"><i class="bi bi-save"></i> Lưu</button>
            </div>
        </form>
    </div>

    {{-- Tab Phụ cấp --}}
    <div class="tab-pane fade" id="tab-allowance">
        <form method="POST" action="{{ route('allowance.store', $employee) }}" class="row g-2 align-items-end mb-3">
            @csrf
            <input type="hidden" name="year" value="{{ $year }}">
            <input type="hidden" name="month" value="{{ $month }}">
            <div class="col-md-3">
                <label class="form-label">Tên phụ cấp</label>
                <input name="name" class="form-control" placeholder="VD: Xăng xe" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Loại</label>
                <select name="type" class="form-select" required>
                    <option value="taxable">Có thuế (cộng vào TNTT)</option>
                    <option value="non_taxable">Không thuế</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Số tiền</label>
                <input type="number" step="1000" name="amount" class="form-control" required>
            </div>
            <div class="col-md-3">
                <button class="btn btn-primary w-100"><i class="bi bi-plus-lg"></i> Thêm</button>
            </div>
        </form>
        <table class="gz-table">
            <thead><tr><th>Tên</th><th>Loại</th><th class="money">Số tiền</th><th></th></tr></thead>
            <tbody>
            @forelse ($allowances as $a)
                <tr>
                    <td>{{ $a->name }}</td>
                    <td>
                        @if ($a->type === 'taxable')
                            <span class="badge bg-warning">Có thuế</span>
                        @else
                            <span class="badge bg-success">Không thuế</span>
                        @endif
                    </td>
                    <td class="money">{{ $fmt($a->amount) }}</td>
                    <td class="text-end">
                        <div class="gz-actions">
                            <form method="POST" action="{{ route('allowance.destroy', $a) }}">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="Xóa phụ cấp"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center" style="color:var(--gz-muted);"><em>Chưa có phụ cấp</em></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- Tab Tạm ứng --}}
    <div class="tab-pane fade" id="tab-advance">
        <form method="POST" action="{{ route('advance.store', $employee) }}" class="row g-2 align-items-end mb-3">
            @csrf
            <input type="hidden" name="year" value="{{ $year }}">
            <input type="hidden" name="month" value="{{ $month }}">
            <div class="col-md-3">
                <label class="form-label">Số tiền tạm ứng</label>
                <input type="number" step="1000" name="amount" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Ngày tạm ứng</label>
                <input type="date" name="advance_date" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">Ghi chú</label>
                <input name="note" class="form-control">
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100"><i class="bi bi-plus-lg"></i> Thêm</button>
            </div>
        </form>
        <table class="gz-table">
            <thead><tr><th>Ngày</th><th class="money">Số tiền</th><th>Ghi chú</th><th></th></tr></thead>
            <tbody>
            @forelse ($advances as $a)
                <tr>
                    <td>{{ optional($a->advance_date)->format('d/m/Y') }}</td>
                    <td class="money">{{ $fmt($a->amount) }}</td>
                    <td><em>{{ $a->note }}</em></td>
                    <td class="text-end">
                        <div class="gz-actions">
                            <form method="POST" action="{{ route('advance.destroy', $a) }}">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="Xóa tạm ứng"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center" style="color:var(--gz-muted);"><em>Chưa có tạm ứng</em></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('month-nav')?.addEventListener('change', function () {
        const base = this.dataset.base;
        location.href = base.replace(/\/0$/, '/' + this.value);
    });
</script>
@endpush
@endsection
