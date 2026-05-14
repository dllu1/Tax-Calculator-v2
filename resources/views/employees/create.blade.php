@extends('layouts.app')
@section('title', $employee->exists ? 'Sửa nhân viên' : 'Thêm nhân viên')

@section('content')
@php $isEdit = $employee->exists; @endphp
<div class="card shadow-sm">
    <div class="card-body">
        <h4><i class="bi bi-person-{{ $isEdit ? 'gear' : 'plus' }}"></i>
            {{ $isEdit ? 'Sửa nhân viên: '.$employee->full_name : 'Thêm nhân viên mới' }}
        </h4>

        <form method="POST" action="{{ $isEdit ? route('employees.update', $employee) : route('employees.store') }}">
            @csrf
            @if ($isEdit) @method('PUT') @endif

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Mã nhân viên <span class="text-danger">*</span></label>
                    <input name="employee_code" class="form-control" required
                           value="{{ old('employee_code', $employee->employee_code) }}">
                </div>
                <div class="col-md-8">
                    <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                    <input name="full_name" class="form-control" required
                           value="{{ old('full_name', $employee->full_name) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Chức vụ</label>
                    <input name="position" class="form-control" value="{{ old('position', $employee->position) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Phòng ban</label>
                    <input name="department" class="form-control" value="{{ old('department', $employee->department) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Ngày vào làm</label>
                    <input type="date" name="joined_date" class="form-control"
                           value="{{ old('joined_date', optional($employee->joined_date)->format('Y-m-d')) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Số người phụ thuộc</label>
                    <input type="number" name="dependents" min="0" max="20" class="form-control"
                           value="{{ old('dependents', $employee->dependents ?? 0) }}">
                </div>
                <div class="col-md-4 d-flex align-items-center pt-3">
                    <div class="form-check form-switch">
                        <input type="hidden" name="is_active" value="0">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1"
                               {{ old('is_active', $employee->is_active ?? true) ? 'checked' : '' }} id="is_active">
                        <label class="form-check-label" for="is_active">Đang làm việc</label>
                    </div>
                </div>

                <div class="col-12"><hr><h5>Lương & phụ cấp cố định</h5></div>

                <div class="col-md-4">
                    <label class="form-label">Lương căn bản (dùng tính thuế) <span class="text-danger">*</span></label>
                    <input type="number" step="1000" name="basic_salary" class="form-control money" required
                           value="{{ old('basic_salary', $employee->basic_salary ?? 0) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Mức lương đóng BHXH <span class="text-danger">*</span></label>
                    <input type="number" step="1000" name="bhxh_salary" class="form-control money" required
                           value="{{ old('bhxh_salary', $employee->bhxh_salary ?? 0) }}">
                    <small class="text-muted">BHXH = mức này × 10,5%</small>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tiền chuyên cần (đủ công)</label>
                    <input type="number" step="1000" name="diligence_bonus" class="form-control money"
                           value="{{ old('diligence_bonus', $employee->diligence_bonus ?? 0) }}">
                </div>
            </div>

            <div class="mt-4">
                <button class="btn btn-primary"><i class="bi bi-check-lg"></i> Lưu</button>
                <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary">Hủy</a>
            </div>
        </form>
    </div>
</div>
@endsection