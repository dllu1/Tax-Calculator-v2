@extends('layouts.app')
@section('title', 'Sổ Nhân Viên')

@section('content')

<div class="gz-section-rule">
    <span class="gz-section-rule-text"><em>I</em> Sổ Nhân Sự</span>
</div>

<div class="gz-card-head" style="margin-bottom: 1rem;">
    <div>
        <h2 class="gz-section-title mb-1">Danh sách nhân viên</h2>
        <p class="gz-section-lede mb-0">
            Quản lý hồ sơ lương căn bản, bảo hiểm và người phụ thuộc.
        </p>
    </div>
    <a href="{{ route('employees.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Thêm Nhân Viên
    </a>
</div>

<div class="gz-card">
    <form method="GET" class="mb-3">
        <div class="d-flex gap-2 align-items-end">
            <div class="flex-grow-1">
                <label class="form-label">Tìm theo mã hoặc họ tên</label>
                <input name="q" class="form-control" placeholder="VD: NV001 hoặc Nguyễn..." value="{{ request('q') }}">
            </div>
            <button class="btn btn-outline-primary"><i class="bi bi-search"></i> Tìm</button>
        </div>
    </form>

    <div class="table-responsive">
    <table class="gz-table">
        <thead>
            <tr>
                <th style="width:90px">Mã NV</th>
                <th>Họ và tên</th>
                <th>Chức vụ</th>
                <th class="money">Lương căn bản</th>
                <th class="money">Lương BHXH</th>
                <th class="num" style="width:60px">NPT</th>
                <th style="width:110px">Trạng thái</th>
                <th style="width:160px"></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($employees as $e)
            <tr>
                <td><strong>{{ $e->employee_code }}</strong></td>
                <td>{{ $e->full_name }}</td>
                <td><em>{{ $e->position }}</em></td>
                <td class="money">{{ number_format($e->basic_salary, 0, ',', '.') }}</td>
                <td class="money">{{ number_format($e->bhxh_salary, 0, ',', '.') }}</td>
                <td class="num">{{ $e->dependents }}</td>
                <td>
                    @if ($e->is_active)
                        <span class="badge bg-success">Đang làm</span>
                    @else
                        <span class="badge bg-secondary">Nghỉ</span>
                    @endif
                </td>
                <td class="text-end">
                    <div class="gz-actions">
                        <a href="{{ route('payroll.show', [$e->id, now()->year, now()->month]) }}"
                           class="btn btn-sm btn-outline-info" title="Phiếu lương">
                            <i class="bi bi-receipt"></i>
                        </a>
                        <a href="{{ route('employees.edit', $e) }}" class="btn btn-sm btn-outline-warning" title="Sửa">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('employees.destroy', $e) }}" method="POST"
                              onsubmit="return confirm('Xóa nhân viên {{ $e->full_name }}?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" title="Xóa"><i class="bi bi-trash"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="8" class="text-center" style="color:var(--gz-muted); padding:2rem;">
                <em>Chưa có nhân viên nào trong sổ.</em>
            </td></tr>
            @endforelse
        </tbody>
    </table>
    </div>

    <div class="mt-3">
        {{ $employees->links() }}
    </div>
</div>

@endsection
