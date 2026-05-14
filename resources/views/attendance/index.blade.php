@extends('layouts.app')
@section('title', 'Chấm công ngày '.$date->format('d/m/Y'))

@section('content')

<div class="gz-section-rule">
    <span class="gz-section-rule-text"><em>I</em> Bảng Chấm Công</span>
</div>

<div class="gz-card-head" style="margin-bottom: 1rem;">
    <div>
        <h2 class="gz-section-title mb-1">
            Chấm công ngày {{ $date->format('d/m/Y') }}
            @if ($date->isToday())
                <span class="badge solid">Hôm nay</span>
            @endif
            @if ($date->isSunday())
                <span class="badge bg-warning">Chủ nhật · ×2 công</span>
            @endif
        </h2>
        <p class="gz-section-lede mb-0">
            Ghi nhận trạng thái làm việc trong ngày — bấm một trạng thái cho mỗi nhân viên,
            nhập số ca tăng ca (mỗi ca = 3 giờ) rồi lưu.
        </p>
    </div>
    <a href="{{ route('attendance.month', ['year' => $date->year, 'month' => $date->month]) }}"
       class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-grid-3x3"></i> Xem Cả Tháng
    </a>
</div>

<div class="gz-card">
    <form method="GET" class="row g-2 align-items-end mb-3">
        <div class="col-md-4">
            <label class="form-label">Chọn ngày</label>
            <input type="date" name="date" value="{{ $date->format('Y-m-d') }}"
                   class="form-control form-control-lg" onchange="this.form.submit()">
        </div>
        <div class="col-md-auto d-flex gap-1">
            <a href="{{ route('attendance.index', ['date' => $date->copy()->subDay()->format('Y-m-d')]) }}"
               class="btn btn-outline-secondary"><i class="bi bi-chevron-left"></i> Hôm Trước</a>
            <a href="{{ route('attendance.index') }}" class="btn btn-outline-primary">Hôm Nay</a>
            <a href="{{ route('attendance.index', ['date' => $date->copy()->addDay()->format('Y-m-d')]) }}"
               class="btn btn-outline-secondary">Hôm Sau <i class="bi bi-chevron-right"></i></a>
        </div>
    </form>

    <div class="alert alert-light small mb-3">
        <strong>Hướng dẫn:</strong> Bấm 1 nút trạng thái cho mỗi nhân viên. Nếu có tăng ca, nhập số ca (mỗi ca = 3 giờ).
        Cuối cùng bấm <strong>"Lưu Chấm Công"</strong> ở dưới.
    </div>

    <form method="POST" action="{{ route('attendance.save') }}" id="attForm">
        @csrf
        <input type="hidden" name="date" value="{{ $date->format('Y-m-d') }}">

        <div class="d-flex gap-2 align-items-center mb-3 flex-wrap">
            <span class="gz-label">Điền nhanh cho cả danh sách:</span>
            <button type="button" class="btn btn-sm btn-outline-success" onclick="fillAll('{{ $date->isSunday() ? 'sunday' : 'normal' }}')">
                <i class="bi bi-check-all"></i> Đi Làm Hết
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="fillAll('')">
                <i class="bi bi-x-circle"></i> Xóa Hết
            </button>
        </div>

        <div class="table-responsive">
        <table class="gz-table align-middle">
            <thead>
                <tr>
                    <th style="width:48px">#</th>
                    <th>Nhân viên</th>
                    <th style="min-width:480px">Trạng thái</th>
                    <th style="width:140px" class="num">Tăng ca (ca 3h)</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($employees as $emp)
                @php
                    $att = $attendances->get($emp->id);
                    $ot = $overtimes->get($emp->id);
                    $currentType = $att?->type ?? '';
                @endphp
                <tr>
                    <td><strong>{{ $loop->iteration }}</strong></td>
                    <td>
                        <div class="fw-bold">{{ $emp->full_name }}</div>
                        <small class="text-muted"><em>{{ $emp->employee_code }} · {{ $emp->position }}</em></small>
                    </td>
                    <td>
                        <div class="btn-group" role="group" data-emp="{{ $emp->id }}">
                            @php
                                $options = [
                                    ['val' => 'normal', 'label' => 'Đi làm',     'icon' => 'bi-check-lg',  'class' => 'success'],
                                    ['val' => 'sunday', 'label' => 'Chủ nhật',   'icon' => 'bi-sun',       'class' => 'warning'],
                                    ['val' => 'leave',  'label' => 'Có phép',    'icon' => 'bi-bookmark',  'class' => 'secondary'],
                                    ['val' => 'absent', 'label' => 'Không phép', 'icon' => 'bi-x-lg',      'class' => 'danger'],
                                ];
                            @endphp
                            @foreach ($options as $opt)
                                <input type="radio" class="btn-check"
                                       name="rows[{{ $emp->id }}][type]"
                                       id="r_{{ $emp->id }}_{{ $opt['val'] }}"
                                       value="{{ $opt['val'] }}"
                                       {{ $currentType === $opt['val'] ? 'checked' : '' }}>
                                <label class="btn btn-outline-{{ $opt['class'] }} btn-sm" for="r_{{ $emp->id }}_{{ $opt['val'] }}">
                                    <i class="bi {{ $opt['icon'] }}"></i> {{ $opt['label'] }}
                                </label>
                            @endforeach
                        </div>
                    </td>
                    <td class="num">
                        <input type="number" min="0" max="5"
                               name="rows[{{ $emp->id }}][overtime_shifts]"
                               class="form-control text-center"
                               value="{{ $ot?->shifts ?? 0 }}"
                               placeholder="0">
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center" style="color:var(--gz-muted); padding:2rem;">
                    <em>Chưa có nhân viên</em>
                </td></tr>
            @endforelse
            </tbody>
        </table>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3 pt-3"
             style="border-top:1px solid var(--gz-rule);">
            <small class="text-muted"><em>{{ $employees->count() }} nhân viên · Ngày {{ $date->format('d/m/Y') }}</em></small>
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="bi bi-save-fill"></i> Lưu Chấm Công
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function fillAll(value) {
    document.querySelectorAll('[data-emp]').forEach(group => {
        const empId = group.dataset.emp;
        group.querySelectorAll('input[type=radio]').forEach(r => r.checked = false);
        if (value) {
            const target = document.getElementById(`r_${empId}_${value}`);
            if (target) target.checked = true;
        }
    });
}
</script>
@endpush
@endsection
