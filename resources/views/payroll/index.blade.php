@extends('layouts.app')
@section('title', 'Bảng lương '.$month.'/'.$year)

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0"><i class="bi bi-cash-stack"></i> Bảng lương {{ $month }}/{{ $year }}</h4>
            <form method="GET" class="d-flex gap-2">
                <select name="month" class="form-select form-select-sm">
                    @for ($m=1; $m<=12; $m++)
                        <option value="{{ $m }}" @selected($m == $month)>Tháng {{ $m }}</option>
                    @endfor
                </select>
                <input type="number" name="year" class="form-control form-control-sm" value="{{ $year }}" style="width:90px">
                <button class="btn btn-sm btn-primary">Tính lại</button>
            </form>
        </div>

        <div class="table-responsive">
        <table class="table table-bordered table-sm align-middle">
            <thead class="table-light">
                <tr>
                    <th>Mã NV</th>
                    <th>Họ tên</th>
                    <th>Công thường</th>
                    <th>CN</th>
                    <th>TC</th>
                    <th class="money">Tổng thực nhận</th>
                    <th class="money">BHXH</th>
                    <th class="money">Thuế TNCN</th>
                    <th class="money">Tạm ứng</th>
                    <th class="money">Còn lại</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($payrolls as $p)
                <tr>
                    <td><strong>{{ $p->employee->employee_code }}</strong></td>
                    <td>{{ $p->employee->full_name }}</td>
                    <td class="text-center">{{ $p->normal_days }}</td>
                    <td class="text-center">{{ $p->sunday_days }}</td>
                    <td class="text-center">{{ $p->overtime_shifts }}</td>
                    <td class="money">{{ number_format($p->total_income, 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($p->bhxh_amount, 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($p->pit_amount, 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($p->advance, 0, ',', '.') }}</td>
                    <td class="money fw-bold text-success">{{ number_format($p->net_salary, 0, ',', '.') }}</td>
                    <td>
                        <a href="{{ route('payroll.show', [$p->employee_id, $year, $month]) }}"
                           class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></a>
                    </td>
                </tr>
                @endforeach
                @if ($payrolls->isEmpty())
                <tr><td colspan="11" class="text-center text-muted">Chưa có nhân viên để tính lương</td></tr>
                @endif
            </tbody>
            <tfoot class="table-light">
                <tr class="fw-bold">
                    <td colspan="5" class="text-end">TỔNG:</td>
                    <td class="money">{{ number_format($payrolls->sum('total_income'), 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($payrolls->sum('bhxh_amount'), 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($payrolls->sum('pit_amount'), 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($payrolls->sum('advance'), 0, ',', '.') }}</td>
                    <td class="money text-success">{{ number_format($payrolls->sum('net_salary'), 0, ',', '.') }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
        </div>
    </div>
</div>
@endsection