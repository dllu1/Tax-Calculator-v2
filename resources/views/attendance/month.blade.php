@extends('layouts.app')
@section('title', 'Chấm công tháng '.$month.'/'.$year)

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <h4 class="mb-0"><i class="bi bi-grid-3x3"></i> Tổng quan chấm công {{ $month }}/{{ $year }}</h4>
            <div class="d-flex gap-2">
                <form method="GET" class="d-flex gap-2">
                    <select name="month" class="form-select form-select-sm">
                        @for ($m=1; $m<=12; $m++)
                            <option value="{{ $m }}" @selected($m == $month)>Tháng {{ $m }}</option>
                        @endfor
                    </select>
                    <input type="number" name="year" class="form-control form-control-sm" value="{{ $year }}" style="width:90px">
                    <button class="btn btn-sm btn-primary">Xem</button>
                </form>
                <a href="{{ route('attendance.index') }}" class="btn btn-sm btn-success">
                    <i class="bi bi-calendar-day"></i> Chấm công hôm nay
                </a>
            </div>
        </div>

        <div class="alert alert-info py-2 small mb-3">
            <strong>Chú thích:</strong>
            <span class="badge bg-success">N</span> Ngày thường ·
            <span class="badge bg-warning text-dark">CN</span> Chủ nhật (×2 công) ·
            <span class="badge bg-secondary">P</span> Có phép ·
            <span class="badge bg-danger">X</span> Không phép ·
            <span class="text-muted">Số nhỏ = số ca tăng ca</span>
        </div>

        <div class="table-responsive" style="max-height: 600px; overflow:auto;">
        <table class="table table-bordered table-sm align-middle" style="min-width: 1200px;">
            <thead class="table-light sticky-top">
                <tr>
                    <th rowspan="2" style="min-width:200px">Nhân viên</th>
                    <th colspan="{{ $end->day }}" class="text-center">Ngày trong tháng {{ $month }}/{{ $year }}</th>
                </tr>
                <tr>
                    @for ($d=1; $d <= $end->day; $d++)
                        @php $date = $start->copy()->day($d); @endphp
                        <th class="text-center {{ $date->isSunday() ? 'bg-warning-subtle' : '' }}" style="min-width:46px">
                            {{ $d }}<br><small>{{ ['CN','T2','T3','T4','T5','T6','T7'][$date->dayOfWeek] }}</small>
                        </th>
                    @endfor
                </tr>
            </thead>
            <tbody>
            @foreach ($employees as $emp)
            <tr>
                <td>
                    <strong>{{ $emp->employee_code }}</strong><br>
                    <small>{{ $emp->full_name }}</small>
                </td>
                @for ($d=1; $d <= $end->day; $d++)
                    @php
                        $date = $start->copy()->day($d);
                        $dateKey = $emp->id.'|'.$date->format('Y-m-d');
                        $att = $attendances->get($dateKey)?->first();
                        $ot = $overtimes->get($dateKey)?->first();
                        $cls = match ($att?->type) {
                            'normal'  => 'bg-success text-white',
                            'sunday'  => 'bg-warning',
                            'leave'   => 'bg-secondary text-white',
                            'absent'  => 'bg-danger text-white',
                            default   => '',
                        };
                        $letter = match ($att?->type) {
                            'normal' => 'N', 'sunday' => 'CN', 'leave' => 'P', 'absent' => 'X', default => '—',
                        };
                    @endphp
                    <td class="text-center {{ $cls }}">
                        <a href="{{ route('attendance.index', ['date' => $date->format('Y-m-d')]) }}"
                           class="text-decoration-none {{ $cls ? 'text-white' : '' }}"
                           title="Chấm ngày {{ $date->format('d/m/Y') }}">
                            <strong>{{ $letter }}</strong>
                            @if ($ot && $ot->shifts > 0)
                                <br><small>+{{ $ot->shifts }}</small>
                            @endif
                        </a>
                    </td>
                @endfor
            </tr>
            @endforeach
            </tbody>
        </table>
        </div>

        <small class="text-muted">Bấm vào ô bất kỳ để chuyển sang trang chấm công ngày đó.</small>
    </div>
</div>
@endsection