@extends('layouts.app')
@section('title', __('Chấm công ngày').' '.$date->format('d/m/Y'))

@section('content')

<div class="gz-section-rule">
    <span class="gz-section-rule-text"><em>I</em> {{ __('Bảng Chấm Công') }}</span>
</div>

<div class="gz-card-head" style="margin-bottom: 1rem;">
    <div>
        <h2 class="gz-section-title mb-1">
            {{ __('Chấm công ngày') }} {{ $date->format('d/m/Y') }}
            @if ($date->isToday())
                <span class="badge solid">{{ __('Hôm nay') }}</span>
            @endif
            @if ($date->isSunday())
                <span class="badge bg-warning">{{ __('Chủ nhật · ×2 công') }}</span>
            @endif
        </h2>
        <p class="gz-section-lede mb-0">
            {{ __('Ghi nhận trạng thái làm việc trong ngày — bấm một trạng thái cho mỗi nhân viên, nhập số ca tăng ca (mỗi ca = 3 giờ) rồi lưu.') }}
        </p>
    </div>
    <a href="{{ route('attendance.month', ['year' => $date->year, 'month' => $date->month]) }}"
       class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-grid-3x3"></i> {{ __('Xem Cả Tháng') }}
    </a>
</div>

<div class="gz-card">
    <form method="GET" class="row g-2 align-items-end mb-3">
        <div class="col-md-4">
            <label class="form-label">{{ __('Chọn ngày') }}</label>
            <input type="date" name="date" value="{{ $date->format('Y-m-d') }}"
                   class="form-control form-control-lg" onchange="this.form.submit()">
        </div>
        <div class="col-md-auto d-flex gap-1">
            <a href="{{ route('attendance.index', ['date' => $date->copy()->subDay()->format('Y-m-d')]) }}"
               class="btn btn-outline-secondary"><i class="bi bi-chevron-left"></i> {{ __('Hôm Trước') }}</a>
            <a href="{{ route('attendance.index') }}" class="btn btn-outline-primary">{{ __('Hôm Nay') }}</a>
            <a href="{{ route('attendance.index', ['date' => $date->copy()->addDay()->format('Y-m-d')]) }}"
               class="btn btn-outline-secondary">{{ __('Hôm Sau') }} <i class="bi bi-chevron-right"></i></a>
        </div>
    </form>

    <div class="alert alert-light small mb-3">
        <strong>{{ __('Hướng dẫn:') }}</strong> {{ __('Bấm 1 nút trạng thái cho mỗi nhân viên. Nếu có tăng ca, nhập số ca (mỗi ca = 3 giờ).') }}
        {{ __('Cuối cùng bấm') }} <strong>"{{ __('Lưu Chấm Công') }}"</strong> {{ __('ở dưới.') }}
    </div>

    <form method="POST" action="{{ route('attendance.save') }}" id="attForm">
        @csrf
        <input type="hidden" name="date" value="{{ $date->format('Y-m-d') }}">

        <div class="d-flex gap-2 align-items-center mb-3 flex-wrap">
            <span class="gz-label">{{ __('Điền nhanh cho cả danh sách:') }}</span>
            <button type="button" class="btn btn-sm btn-outline-success js-fill-all" data-value="{{ $date->isSunday() ? 'sunday' : 'normal' }}">
                <i class="bi bi-check-all"></i> {{ __('Đi Làm Hết') }}
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger js-fill-all" data-value="">
                <i class="bi bi-x-circle"></i> {{ __('Xóa Hết') }}
            </button>
        </div>

        <div class="table-responsive">
        <table class="gz-table align-middle">
            <thead>
                <tr>
                    <th style="width:48px">#</th>
                    <th>{{ __('Nhân viên') }}</th>
                    <th style="min-width:480px">{{ __('Trạng thái') }}</th>
                    <th style="width:140px" class="num">{{ __('Tăng ca (ca 3h)') }}</th>
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
                                    ['val' => 'normal', 'label' => __('Đi làm'),     'icon' => 'bi-check-lg',  'class' => 'success'],
                                    ['val' => 'half',   'label' => __('Nửa ngày'),   'icon' => 'bi-circle-half','class' => 'info'],
                                    ['val' => 'sunday', 'label' => __('Chủ nhật'),   'icon' => 'bi-sun',       'class' => 'warning'],
                                    ['val' => 'leave',  'label' => __('Có phép'),    'icon' => 'bi-bookmark',  'class' => 'secondary'],
                                    ['val' => 'absent', 'label' => __('Không phép'), 'icon' => 'bi-x-lg',      'class' => 'danger'],
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
                    <em>{{ __('Chưa có nhân viên') }}</em>
                </td></tr>
            @endforelse
            </tbody>
        </table>
        </div>

        <div class="att-save-bar d-flex justify-content-between align-items-center">
            <small class="text-muted"><em>{{ $employees->count() }} {{ __('nhân viên') }} · {{ __('Ngày') }} {{ $date->format('d/m/Y') }}</em></small>
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="bi bi-save-fill"></i> {{ __('Lưu Chấm Công') }}
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
(function () {
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
    document.querySelectorAll('.js-fill-all').forEach(btn => {
        btn.addEventListener('click', () => fillAll(btn.dataset.value || ''));
    });

    // Detect when the save bar is "stuck" (floating above hidden content) vs at its
    // natural end-of-form position. When the element BEFORE the bar (the table wrapper)
    // has its bottom edge above viewport bottom minus bar height, the bar has reached
    // its natural slot and should shrink to container width.
    const bar = document.querySelector('.att-save-bar');
    if (bar) {
        const sentinel = bar.previousElementSibling;
        const updateStuck = () => {
            if (!sentinel) { bar.classList.add('is-stuck'); return; }
            const sRect = sentinel.getBoundingClientRect();
            const barH = bar.offsetHeight;
            bar.classList.toggle('is-stuck', sRect.bottom > window.innerHeight - barH - 1);
        };
        updateStuck();
        window.addEventListener('scroll', updateStuck, { passive: true });
        window.addEventListener('resize', updateStuck);
    }
})();
</script>
@endpush
@endsection
