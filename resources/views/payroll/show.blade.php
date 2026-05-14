@extends('layouts.app')
@section('title', 'Phiếu lương '.$employee->full_name)

@php
    $fmt = fn($n) => number_format($n, 0, ',', '.');
@endphp

@section('content')
<div class="row g-3">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h4 class="mb-1">
                            <i class="bi bi-receipt"></i>
                            Phiếu lương: {{ $employee->full_name }}
                            <span class="badge bg-primary">{{ $employee->employee_code }}</span>
                        </h4>
                        <div class="text-muted">
                            {{ $employee->position }} · {{ $employee->department }} ·
                            NPT: {{ $employee->dependents }}
                        </div>
                    </div>
                    <form method="GET" class="d-flex gap-2">
                        <select name="month" class="form-select form-select-sm" onchange="location.href='{{ route('payroll.show', [$employee->id, $year, '__m__']) }}'.replace('__m__', this.value)">
                            @for ($m=1; $m<=12; $m++)
                                <option value="{{ $m }}" @selected($m == $month)>Tháng {{ $m }}</option>
                            @endfor
                        </select>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Bảng tính lương --}}
    <div class="col-md-7">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-cash"></i> Lương tháng {{ $month }}/{{ $year }}
            </div>
            <table class="table table-sm mb-0">
                <tbody>
                <tr><th colspan="2" class="bg-light">CÔNG & TĂNG CA</th></tr>
                <tr><td>Ngày công thường</td><td class="money">{{ $payroll->normal_days }} ngày</td></tr>
                <tr><td>Ngày chủ nhật (×2)</td><td class="money">{{ $payroll->sunday_days }} ngày</td></tr>
                <tr><td>Số ca tăng ca (3h = ½ ngày)</td><td class="money">{{ $payroll->overtime_shifts }} ca</td></tr>
                <tr><td>Ngày nghỉ không phép</td><td class="money text-danger">{{ $payroll->absent_days }}</td></tr>

                <tr><th colspan="2" class="bg-light">CÁC KHOẢN THU NHẬP</th></tr>
                <tr><td>Lương ngày công</td><td class="money">{{ $fmt($payroll->day_wage) }}</td></tr>
                <tr><td>Lương tăng ca</td><td class="money">{{ $fmt($payroll->overtime_wage) }}</td></tr>
                <tr><td>Tiền ăn giữa ca (30.000/ngày)</td><td class="money">{{ $fmt($payroll->meal_shift) }}</td></tr>
                <tr><td>Tiền ăn tăng ca (30.000/ca)</td><td class="money">{{ $fmt($payroll->meal_overtime) }}</td></tr>
                <tr><td>Lương sản phẩm</td><td class="money">{{ $fmt($payroll->product_salary) }}</td></tr>
                <tr><td>Chuyên cần</td><td class="money">{{ $fmt($payroll->diligence) }}</td></tr>
                <tr><td>Phụ cấp chịu thuế</td><td class="money">{{ $fmt($payroll->taxable_allowances) }}</td></tr>
                <tr><td>Phụ cấp không chịu thuế</td><td class="money">{{ $fmt($payroll->non_taxable_allowances) }}</td></tr>
                <tr class="table-info fw-bold">
                    <td>TỔNG THỰC NHẬN</td>
                    <td class="money">{{ $fmt($payroll->total_income) }}</td>
                </tr>

                <tr><th colspan="2" class="bg-light">KHẤU TRỪ</th></tr>
                <tr><td>BHXH (10,5% × {{ $fmt($employee->bhxh_salary) }})</td>
                    <td class="money text-danger">−{{ $fmt($payroll->bhxh_amount) }}</td></tr>
                <tr><td>Thuế TNCN</td>
                    <td class="money text-danger">−{{ $fmt($payroll->pit_amount) }}</td></tr>
                <tr><td>Tạm ứng</td>
                    <td class="money text-danger">−{{ $fmt($payroll->advance) }}</td></tr>

                <tr class="table-success fw-bold fs-5">
                    <td>TIỀN LƯƠNG CÒN LẠI</td>
                    <td class="money">{{ $fmt($payroll->net_salary) }}</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Bảng tính thuế --}}
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-header bg-warning">
                <i class="bi bi-percent"></i> Chi tiết tính thuế TNCN
            </div>
            <table class="table table-sm mb-0">
                <tbody>
                <tr><td>Lương căn bản</td><td class="money">{{ $fmt($employee->basic_salary) }}</td></tr>
                <tr><td>+ Lương sản phẩm</td><td class="money">{{ $fmt($payroll->product_salary) }}</td></tr>
                <tr><td>+ Phụ cấp chịu thuế</td><td class="money">{{ $fmt($payroll->taxable_allowances) }}</td></tr>
                <tr class="table-info fw-bold">
                    <td>= Thu nhập tính thuế</td>
                    <td class="money">{{ $fmt($payroll->taxable_income) }}</td>
                </tr>

                <tr><td>− Giảm trừ bản thân</td>
                    <td class="money text-success">{{ $fmt($payroll->personal_deduction) }}</td></tr>
                <tr><td>− Giảm trừ NPT ({{ $employee->dependents }} người × 4,4tr)</td>
                    <td class="money text-success">{{ $fmt($payroll->dependent_deduction) }}</td></tr>
                <tr><td>− BHXH 10,5%</td>
                    <td class="money text-success">{{ $fmt($payroll->bhxh_amount) }}</td></tr>

                <tr class="table-warning fw-bold">
                    <td>= Thu nhập chịu thuế</td>
                    <td class="money">{{ $fmt($payroll->assessable_income) }}</td>
                </tr>
                <tr class="table-danger fw-bold fs-5">
                    <td>Thuế TNCN phải nộp</td>
                    <td class="money">{{ $fmt($payroll->pit_amount) }}</td>
                </tr>

                @if (!empty($payroll->detail['pit_rate']))
                <tr><td colspan="2" class="small text-muted">
                    Áp bậc thuế: <strong>{{ $payroll->detail['pit_rate'] * 100 }}%</strong> –
                    khấu trừ: {{ $fmt($payroll->detail['pit_deduction']) }}
                </td></tr>
                @endif
                </tbody>
            </table>
        </div>
    </div>

    {{-- Quản lý dữ liệu tháng --}}
    <div class="col-md-12">
        <ul class="nav nav-tabs" id="dataTabs" role="tablist">
            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-product">
                Lương sản phẩm</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-allowance">
                Phụ cấp</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-advance">
                Tạm ứng</button></li>
        </ul>
        <div class="tab-content border border-top-0 p-3 bg-white">
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
                <table class="table table-sm">
                    <thead><tr><th>Tên</th><th>Loại</th><th class="money">Số tiền</th><th></th></tr></thead>
                    <tbody>
                    @forelse ($allowances as $a)
                        <tr>
                            <td>{{ $a->name }}</td>
                            <td>
                                @if ($a->type === 'taxable')
                                    <span class="badge bg-warning text-dark">Có thuế</span>
                                @else
                                    <span class="badge bg-success">Không thuế</span>
                                @endif
                            </td>
                            <td class="money">{{ $fmt($a->amount) }}</td>
                            <td>
                                <form method="POST" action="{{ route('allowance.destroy', $a) }}" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted">Chưa có phụ cấp</td></tr>
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
                <table class="table table-sm">
                    <thead><tr><th>Ngày</th><th class="money">Số tiền</th><th>Ghi chú</th><th></th></tr></thead>
                    <tbody>
                    @forelse ($advances as $a)
                        <tr>
                            <td>{{ optional($a->advance_date)->format('d/m/Y') }}</td>
                            <td class="money">{{ $fmt($a->amount) }}</td>
                            <td>{{ $a->note }}</td>
                            <td>
                                <form method="POST" action="{{ route('advance.destroy', $a) }}" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted">Chưa có tạm ứng</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection