@extends('layouts.app')
@section('title', $employee->exists ? 'Sửa hồ sơ' : 'Lập hồ sơ mới')

@section('content')
@php $isEdit = $employee->exists; @endphp

<div class="gz-section-rule">
    <span class="gz-section-rule-text"><em>I</em> Hồ Sơ Nhân Viên</span>
</div>

<div class="gz-card-head" style="margin-bottom: 1rem;">
    <div>
        <h2 class="gz-section-title mb-1">
            {{ $isEdit ? 'Sửa hồ sơ: '.$employee->full_name : 'Lập hồ sơ nhân viên mới' }}
        </h2>
        <p class="gz-section-lede mb-0">
            @if ($isEdit)
                Cập nhật thông tin lương, BHXH và người phụ thuộc — áp dụng cho các kỳ lương từ tháng hiện tại.
            @else
                Khai báo thông tin căn bản, lương cố định và bảo hiểm để bắt đầu tính lương hàng tháng.
            @endif
        </p>
    </div>
    <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Về Sổ Nhân Viên
    </a>
</div>

<form method="POST" action="{{ $isEdit ? route('employees.update', $employee) : route('employees.store') }}">
    @csrf
    @if ($isEdit) @method('PUT') @endif

    <div class="gz-card">
        <div class="gz-label mb-3">Thông Tin Căn Bản</div>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Mã nhân viên *</label>
                <input name="employee_code" class="form-control" required
                       value="{{ old('employee_code', $employee->employee_code) }}">
            </div>
            <div class="col-md-8">
                <label class="form-label">Họ và tên *</label>
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
            <div class="col-md-4 d-flex align-items-center pt-4">
                <div class="form-check form-switch">
                    <input type="hidden" name="is_active" value="0">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1"
                           {{ old('is_active', $employee->is_active ?? true) ? 'checked' : '' }} id="is_active">
                    <label class="form-check-label" for="is_active" style="font-style:italic;">Đang làm việc</label>
                </div>
            </div>
        </div>
    </div>

    <div class="gz-card">
        <div class="gz-label mb-3">Lương &amp; Phụ Cấp Cố Định</div>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Lương căn bản (tính thuế) *</label>
                <input type="number" step="1000" name="basic_salary" class="form-control money" required
                       value="{{ old('basic_salary', $employee->basic_salary ?? 0) }}">
                <small class="text-muted"><em>Là cơ sở để tính lương ngày &amp; TN tính thuế</em></small>
            </div>
            <div class="col-md-4">
                <label class="form-label">Mức lương đóng BHXH *</label>
                <input type="number" step="1000" name="bhxh_salary" class="form-control money" required
                       value="{{ old('bhxh_salary', $employee->bhxh_salary ?? 0) }}">
                <small class="text-muted"><em>BHXH = mức này × 10,5%</em></small>
            </div>
            <div class="col-md-4">
                <label class="form-label">Tiền chuyên cần (đủ công)</label>
                <input type="number" step="1000" name="diligence_bonus" class="form-control money"
                       value="{{ old('diligence_bonus', $employee->diligence_bonus ?? 0) }}">
                <small class="text-muted"><em>Trả nếu không nghỉ không phép trong tháng</em></small>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2 justify-content-end">
        <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary">Hủy</a>
        <button class="btn btn-primary"><i class="bi bi-check-lg"></i> Lưu Hồ Sơ</button>
    </div>
</form>

@endsection
