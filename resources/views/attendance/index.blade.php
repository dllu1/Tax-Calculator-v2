@extends('layouts.app')
@section('title', 'Chấm công ngày '.$date->format('d/m/Y'))

@section('content')
<div class="card shadow-sm">
    <div class="card-body">

        {{-- Chọn ngày --}}
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <h4 class="mb-0">
                <i class="bi bi-calendar-check"></i> Chấm công
                @if ($date->isToday())
                    <span class="badge bg-success">HÔM NAY</span>
                @endif
                @if ($date->isSunday())
                    <span class="badge bg-warning text-dark">CHỦ NHẬT (×2 công)</span>
                @endif
            </h4>
            <a href="{{ route('attendance.month', ['year' => $date->year, 'month' => $date->month]) }}"
               class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-grid-3x3"></i> Xem cả tháng
            </a>
        </div>

        <form method="GET" class="row g-2 align-items-end mb-3">
            <div class="col-md-4">
                <label class="form-label small text-muted mb-1">Chọn ngày cần chấm công</label>
                <input type="date" name="date" value="{{ $date->format('Y-m-d') }}"
                       class="form-control form-control-lg" onchange="this.form.submit()">
            </div>
            <div class="col-md-auto">
                <a href="{{ route('attendance.index', ['date' => $date->copy()->subDay()->format('Y-m-d')]) }}"
                   class="btn btn-outline-secondary"><i class="bi bi-chevron-left"></i> Hôm trước</a>
                <a href="{{ route('attendance.index') }}"
                   class="btn btn-outline-primary">Hôm nay</a>
                <a href="{{ route('attendance.index', ['date' => $date->copy()->addDay()->format('Y-m-d')]) }}"
                   class="btn btn-outline-secondary">Hôm sau <i class="bi bi-chevron-right"></i></a>
            </div>
        </form>

        <div class="alert alert-light border small">
            <strong>Hướng dẫn:</strong> Bấm 1 nút trạng thái cho mỗi nhân viên. Nếu có tăng ca, nhập số ca (mỗi ca = 3 giờ).
            Cuối cùng bấm <strong>"Lưu chấm công"</strong> ở dưới.
        </div>

        <form method="POST" action="{{ route('attendance.save') }}" id="attForm">
            @csrf
            <input type="hidden" name="date" value="{{ $date->format('Y-m-d') }}">

            {{-- Nút điền nhanh --}}
            <div class="mb-2 d-flex gap-2 flex-wrap">
                <span class="text-muted small align-self-center">Điền nhanh cho tất cả:</span>
                <button type="button" class="btn btn-sm btn-success" onclick="fillAll('{{ $date->isSunday() ? 'sunday' : 'normal' }}')">
                    <i class="bi bi-check-all"></i> Đi làm hết
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="fillAll('')">
                    <i class="bi bi-x-circle"></i> Xóa hết
                </button>
            </div>

            {{-- Danh sách nhân viên --}}
            <div class="table-responsive">
            <table class="table align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width:60px">#</th>
                        <th>Nhân viên</th>
                        <th style="min-width:480px">Trạng thái</th>
                        <th style="width:160px" class="text-center">Tăng ca (số ca 3h)</th>
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
                            <small class="text-muted">{{ $emp->employee_code }} · {{ $emp->position }}</small>
                        </td>
                        <td>
                            <div class="btn-group w-100" role="group" data-emp="{{ $emp->id }}">
                                @php
                                    $options = [
                                        ['val' => 'normal', 'label' => 'Đi làm',    'icon' => 'bi-check-lg',     'class' => 'success'],
                                        ['val' => 'sunday', 'label' => 'Chủ nhật',  'icon' => 'bi-sun',          'class' => 'warning'],
                                        ['val' => 'leave',  'label' => 'Có phép',   'icon' => 'bi-bookmark',     'class' => 'secondary'],
                                        ['val' => 'absent', 'label' => 'Không phép','icon' => 'bi-x-lg',         'class' => 'danger'],
                                    ];
                                @endphp
                                @foreach ($options as $opt)
                                    <input type="radio" class="btn-check"
                                           name="rows[{{ $emp->id }}][type]"
                                           id="r_{{ $emp->id }}_{{ $opt['val'] }}"
                                           value="{{ $opt['val'] }}"
                                           {{ $currentType === $opt['val'] ? 'checked' : '' }}>
                                    <label class="btn btn-outline-{{ $opt['class'] }}" for="r_{{ $emp->id }}_{{ $opt['val'] }}">
                                        <i class="bi {{ $opt['icon'] }}"></i> {{ $opt['label'] }}
                                    </label>
                                @endforeach
                            </div>
                        </td>
                        <td class="text-center">
                            <input type="number" min="0" max="5"
                                   name="rows[{{ $emp->id }}][overtime_shifts]"
                                   class="form-control text-center"
                                   value="{{ $ot?->shifts ?? 0 }}"
                                   placeholder="0">
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted">Chưa có nhân viên</td></tr>
                @endforelse
                </tbody>
            </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3 sticky-bottom bg-white py-2 border-top">
                <small class="text-muted">{{ $employees->count() }} nhân viên · Ngày {{ $date->format('d/m/Y') }}</small>
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-save-fill"></i> Lưu chấm công
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function fillAll(value) {
    document.querySelectorAll('[data-emp]').forEach(group => {
        const empId = group.dataset.emp;
        // bỏ chọn hết
        group.querySelectorAll('input[type=radio]').forEach(r => r.checked = false);
        // chọn cái mới (nếu value khác rỗng)
        if (value) {
            const target = document.getElementById(`r_${empId}_${value}`);
            if (target) target.checked = true;
        }
    });
}
</script>
@endpush
@endsection