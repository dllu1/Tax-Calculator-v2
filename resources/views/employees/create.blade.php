@extends('layouts.app')
@section('title', $employee->exists ? __('Sửa hồ sơ') : __('Lập hồ sơ mới'))

@section('content')
@php $isEdit = $employee->exists; @endphp

<div class="gz-section-rule">
    <span class="gz-section-rule-text"><em>I</em> {{ __('Hồ Sơ Nhân Viên') }}</span>
</div>

<div class="gz-card-head" style="margin-bottom: 1rem;">
    <div>
        <h2 class="gz-section-title mb-1">
            {{ $isEdit ? __('Sửa hồ sơ: ').$employee->full_name : __('Lập hồ sơ nhân viên mới') }}
        </h2>
        <p class="gz-section-lede mb-0">
            @if ($isEdit)
                {{ __('Cập nhật thông tin lương, thông tin cá nhân và người phụ thuộc.') }}
            @else
                {{ __('Khai báo thông tin căn bản và lương. Sau khi lưu sẽ mở thêm tab Thông Tin Cá Nhân và Người Phụ Thuộc cho quyết toán thuế.') }}
            @endif
        </p>
    </div>
    <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> {{ __('Về Sổ Nhân Viên') }}
    </a>
</div>

@if ($isEdit)
<ul class="nav nav-tabs" role="tablist">
    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-profile">
        {{ __('Hồ Sơ Lương') }}</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-personal">
        {{ __('Thông Tin Cá Nhân') }}</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-dependents">
        {{ __('Người Phụ Thuộc') }} ({{ $employee->dependents ?? 0 }})</button></li>
</ul>
<div class="tab-content gz-tab-body">
@endif

{{-- ============ TAB 1: Hồ Sơ Lương (basic info + salary) ============ --}}
<div class="@if ($isEdit) tab-pane fade show active @endif" id="tab-profile">
<form method="POST" action="{{ $isEdit ? route('employees.update', $employee) : route('employees.store') }}">
    @csrf
    @if ($isEdit) @method('PUT') @endif

    <div class="gz-card">
        <div class="gz-label mb-3">{{ __('Thông Tin Căn Bản') }}</div>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">{{ __('Mã nhân viên') }} *</label>
                <input name="employee_code" class="form-control" required
                       value="{{ old('employee_code', $employee->employee_code) }}">
            </div>
            <div class="col-md-8">
                <label class="form-label">{{ __('Họ và tên') }} *</label>
                <input name="full_name" class="form-control" required
                       value="{{ old('full_name', $employee->full_name) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">{{ __('Chức vụ') }}</label>
                <input name="position" class="form-control" value="{{ old('position', $employee->position) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">{{ __('Phòng ban') }}</label>
                <input name="department" class="form-control" value="{{ old('department', $employee->department) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">{{ __('Ngày vào làm') }}</label>
                <input type="date" name="joined_date" class="form-control"
                       value="{{ old('joined_date', optional($employee->joined_date)->format('Y-m-d')) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">{{ __('Số người phụ thuộc') }}</label>
                <input type="number" class="form-control" disabled
                       value="{{ $employee->dependents ?? 0 }}">
                <small class="text-muted"><em>{{ __('Tự động đếm từ tab "Người Phụ Thuộc"') }}</em></small>
            </div>
            <div class="col-md-4 d-flex align-items-center pt-4">
                <div class="form-check form-switch">
                    <input type="hidden" name="is_active" value="0">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1"
                           {{ old('is_active', $employee->is_active ?? true) ? 'checked' : '' }} id="is_active">
                    <label class="form-check-label" for="is_active" style="font-style:italic;">{{ __('Đang làm việc') }}</label>
                </div>
            </div>
        </div>
    </div>

    <div class="gz-card">
        <div class="gz-label mb-3">{{ __('Lương & Phụ Cấp Cố Định') }}</div>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">{{ __('Lương căn bản') }} *</label>
                <input type="number" step="1000" name="basic_salary" class="form-control money" required
                       value="{{ old('basic_salary', $employee->basic_salary ?? 0) }}">
                <small class="text-muted"><em>{{ __('Là cơ sở để tính lương ngày') }}</em></small>
            </div>
            <div class="col-md-4">
                <label class="form-label">{{ __('Mức lương đóng BHXH') }} *</label>
                <input type="number" step="1000" name="bhxh_salary" class="form-control money" required
                       value="{{ old('bhxh_salary', $employee->bhxh_salary ?? 0) }}">
                <small class="text-muted"><em>{{ __('BHXH = mức này × 10,5%') }}</em></small>
            </div>
            <div class="col-md-4">
                <label class="form-label">{{ __('Tiền chuyên cần (đủ công)') }}</label>
                <input type="number" step="1000" name="diligence_bonus" class="form-control money"
                       value="{{ old('diligence_bonus', $employee->diligence_bonus ?? 0) }}">
                <small class="text-muted"><em>{{ __('Trả nếu không nghỉ không phép trong tháng') }}</em></small>
            </div>
            <div class="col-md-6">
                <label class="form-label">{{ __('Tiền thưởng Tết') }}</label>
                <input type="number" step="1000" name="tet_bonus" class="form-control money"
                       value="{{ old('tet_bonus', $employee->tet_bonus ?? 0) }}">
                <small class="text-muted"><em>{{ __('Cộng vào TN tính thuế. Đặt về 0 sau khi đã chi để không tính tiếp tháng sau.') }}</em></small>
            </div>
            <div class="col-md-6">
                <label class="form-label">{{ __('Lương phép năm') }}</label>
                <input type="number" step="1000" name="annual_leave_pay" class="form-control money"
                       value="{{ old('annual_leave_pay', $employee->annual_leave_pay ?? 0) }}">
                <small class="text-muted"><em>{{ __('Cộng vào TN tính thuế. Đặt về 0 sau khi đã chi để không tính tiếp tháng sau.') }}</em></small>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2 justify-content-end">
        <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary">{{ __('Hủy') }}</a>
        <button class="btn btn-primary"><i class="bi bi-check-lg"></i> {{ __('Lưu Hồ Sơ') }}</button>
    </div>
</form>
</div>

@if ($isEdit)
{{-- ============ TAB 2: Thông Tin Cá Nhân ============ --}}
<div class="tab-pane fade" id="tab-personal">
    <div class="gz-card">
        <div class="gz-label mb-3">{{ __('Thông Tin Cá Nhân — Dùng Cho Quyết Toán Thuế') }}</div>
        <form method="POST" action="{{ route('employees.personal-info', $employee) }}"
              data-ajax="true" data-soft-reload="true">
            @csrf
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">{{ __('Ngày tháng năm sinh') }}</label>
                    <input type="date" name="dob" class="form-control"
                           value="{{ old('dob', optional($employee->dob)->format('Y-m-d')) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">{{ __('Mã số thuế') }}</label>
                    <input name="tax_code" class="form-control" maxlength="20"
                           value="{{ old('tax_code', $employee->tax_code) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">{{ __('Căn cước công dân') }}</label>
                    <input name="id_card" class="form-control" maxlength="20"
                           value="{{ old('id_card', $employee->id_card) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">{{ __('Số điện thoại') }}</label>
                    <input name="phone" class="form-control" maxlength="30"
                           value="{{ old('phone', $employee->phone) }}">
                </div>
                <div class="col-md-8">
                    <label class="form-label">{{ __('Địa chỉ') }}</label>
                    <input name="address" class="form-control" maxlength="255"
                           value="{{ old('address', $employee->address) }}">
                </div>
            </div>
            <div class="d-flex justify-content-end mt-3">
                <button class="btn btn-primary"><i class="bi bi-check-lg"></i> {{ __('Lưu Thông Tin') }}</button>
            </div>
        </form>
    </div>
</div>

{{-- ============ TAB 3: Người Phụ Thuộc ============ --}}
<div class="tab-pane fade" id="tab-dependents">
    <div class="gz-card">
        <div class="gz-label mb-3">{{ __('Thêm Người Phụ Thuộc') }}</div>
        <form method="POST" action="{{ route('employees.dependents.store', $employee) }}"
              data-ajax="true" data-soft-reload="true" data-reset-after="true"
              class="row g-2 align-items-end">
            @csrf
            <div class="col-md-3">
                <label class="form-label">{{ __('Họ tên') }} *</label>
                <input name="name" class="form-control" required placeholder="{{ __('VD: Nguyễn Văn A') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">{{ __('Quan hệ') }}</label>
                <input name="relationship" class="form-control" maxlength="60" placeholder="{{ __('VD: Con, Vợ') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">{{ __('CCCD') }}</label>
                <input name="id_card" class="form-control" maxlength="20">
            </div>
            <div class="col-md-3">
                <label class="form-label">{{ __('Địa chỉ') }}</label>
                <input name="address" class="form-control" maxlength="255">
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100"><i class="bi bi-plus-lg"></i> {{ __('Thêm') }}</button>
            </div>
        </form>

        <table class="gz-table mt-3">
            <thead>
                <tr>
                    <th>{{ __('Họ tên') }}</th>
                    <th>{{ __('Quan hệ') }}</th>
                    <th>{{ __('CCCD') }}</th>
                    <th>{{ __('Địa chỉ') }}</th>
                    <th style="width:60px"></th>
                </tr>
            </thead>
            <tbody>
            @forelse ($employee->dependentRecords as $dep)
                <tr data-dep-row="{{ $dep->id }}">
                    <td><strong>{{ $dep->name }}</strong></td>
                    <td><em>{{ $dep->relationship }}</em></td>
                    <td>{{ $dep->id_card }}</td>
                    <td>{{ $dep->address }}</td>
                    <td class="text-end">
                        <button type="button" class="btn btn-sm btn-outline-danger" title="{{ __('Xóa') }}"
                                data-ajax-delete="{{ route('dependents.destroy', $dep) }}"
                                data-confirm="{{ __('Xóa người phụ thuộc') }} {{ $dep->name }}?"
                                data-remove-row="tr[data-dep-row]"
                                data-soft-reload="true">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center" style="color:var(--gz-muted); padding:1rem;">
                    <em>{{ __('Chưa có người phụ thuộc') }}</em>
                </td></tr>
            @endforelse
            </tbody>
        </table>

        <p class="text-muted small mt-2"><em>
            {{ __('Mỗi lần thêm/xóa, "Số người phụ thuộc" trên tab Hồ Sơ Lương tự cập nhật và áp ngay vào kỳ lương tiếp theo.') }}
        </em></p>
    </div>
</div>

</div> {{-- /.tab-content --}}
@endif

@endsection
