@extends('layouts.app')
@section('title', 'Danh sách nhân viên')

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0"><i class="bi bi-people"></i> Nhân viên</h4>
            <a href="{{ route('employees.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Thêm nhân viên
            </a>
        </div>

        <form method="GET" class="mb-3">
            <input name="q" class="form-control" placeholder="Tìm theo mã / họ tên..." value="{{ request('q') }}">
        </form>

        <div class="table-responsive">
        <table class="table table-hover">
            <thead class="table-light">
                <tr>
                    <th>Mã NV</th>
                    <th>Họ và tên</th>
                    <th>Chức vụ</th>
                    <th class="money">Lương căn bản</th>
                    <th class="money">Lương BHXH</th>
                    <th>NPT</th>
                    <th>Trạng thái</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($employees as $e)
                <tr>
                    <td><strong>{{ $e->employee_code }}</strong></td>
                    <td>{{ $e->full_name }}</td>
                    <td>{{ $e->position }}</td>
                    <td class="money">{{ number_format($e->basic_salary, 0, ',', '.') }}</td>
                    <td class="money">{{ number_format($e->bhxh_salary, 0, ',', '.') }}</td>
                    <td class="text-center">{{ $e->dependents }}</td>
                    <td>
                        @if ($e->is_active)
                            <span class="badge bg-success">Đang làm</span>
                        @else
                            <span class="badge bg-secondary">Nghỉ</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('payroll.show', [$e->id, now()->year, now()->month]) }}"
                           class="btn btn-sm btn-outline-info">
                            <i class="bi bi-cash"></i>
                        </a>
                        <a href="{{ route('employees.edit', $e) }}" class="btn btn-sm btn-outline-warning">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('employees.destroy', $e) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Xóa nhân viên {{ $e->full_name }}?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted">Chưa có nhân viên</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
        {{ $employees->links() }}
    </div>
</div>
@endsection