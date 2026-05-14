@extends('layouts.app')
@section('title', 'Chấm công tháng '.$month.'/'.$year)

@section('content')

<div class="gz-section-rule">
    <span class="gz-section-rule-text"><em>II</em> Toàn Cảnh Trong Tháng</span>
</div>

<div class="gz-card-head" style="margin-bottom: 1rem;">
    <div>
        <h2 class="gz-section-title mb-1">Chấm công tháng {{ $month }}/{{ $year }}</h2>
        <p class="gz-section-lede mb-0">
            Bấm vào bất kỳ ô nào để chuyển sang trang chấm công ngày tương ứng.
        </p>
    </div>
    <div class="d-flex gap-2">
        <form method="GET" class="d-flex gap-2">
            <select name="month" class="form-select form-select-sm">
                @for ($m=1; $m<=12; $m++)
                    <option value="{{ $m }}" @selected($m == $month)>Tháng {{ $m }}</option>
                @endfor
            </select>
            <input type="number" name="year" class="form-control form-control-sm" value="{{ $year }}" style="width:90px">
            <button class="btn btn-sm btn-outline-primary">Xem</button>
        </form>
        <a href="{{ route('attendance.index') }}" class="btn btn-sm btn-primary">
            <i class="bi bi-calendar-day"></i> Chấm Công Hôm Nay
        </a>
    </div>
</div>

<div class="gz-card">
    <div class="d-flex gap-3 flex-wrap mb-3 align-items-center" style="font-size:0.85rem;">
        <span class="gz-label">Chú thích:</span>
        <span><span class="badge att-normal" style="padding:2px 8px; border-radius:0;">N</span> Ngày thường</span>
        <span><span class="badge att-sunday" style="padding:2px 8px; border-radius:0;">CN</span> Chủ nhật (×2 công)</span>
        <span><span class="badge att-leave" style="padding:2px 8px; border-radius:0;">P</span> Có phép</span>
        <span><span class="badge att-absent" style="padding:2px 8px; border-radius:0;">X</span> Không phép</span>
        <span class="text-muted"><em>Số nhỏ ở dưới = số ca tăng ca</em></span>
    </div>

    <div class="table-responsive" style="max-height: 620px; overflow:auto;">
    <table class="gz-grid-table" style="min-width: 1200px;">
        <thead class="sticky-top">
            <tr>
                <th rowspan="2" style="min-width:200px; text-align:left; padding-left:0.6rem;">Nhân viên</th>
                <th colspan="{{ $end->day }}">Ngày trong tháng {{ $month }}/{{ $year }}</th>
            </tr>
            <tr>
                @for ($d=1; $d <= $end->day; $d++)
                    @php $date = $start->copy()->day($d); @endphp
                    <th class="{{ $date->isSunday() ? 'sunday-col' : '' }}" style="min-width:42px">
                        {{ $d }}<br><small style="color:var(--gz-muted);">{{ ['CN','T2','T3','T4','T5','T6','T7'][$date->dayOfWeek] }}</small>
                    </th>
                @endfor
            </tr>
        </thead>
        <tbody>
        @foreach ($employees as $emp)
        <tr>
            <td style="text-align:left; padding-left:0.6rem;">
                <strong>{{ $emp->employee_code }}</strong><br>
                <small style="color:var(--gz-muted);"><em>{{ $emp->full_name }}</em></small>
            </td>
            @for ($d=1; $d <= $end->day; $d++)
                @php
                    $date = $start->copy()->day($d);
                    $dateKey = $emp->id.'|'.$date->format('Y-m-d');
                    $att = $attendances->get($dateKey)?->first();
                    $ot = $overtimes->get($dateKey)?->first();
                    $cls = match ($att?->type) {
                        'normal'  => 'att-normal',
                        'sunday'  => 'att-sunday',
                        'leave'   => 'att-leave',
                        'absent'  => 'att-absent',
                        default   => '',
                    };
                    $letter = match ($att?->type) {
                        'normal' => 'N', 'sunday' => 'CN', 'leave' => 'P', 'absent' => 'X', default => '—',
                    };
                @endphp
                <td class="att-cell {{ $cls }}">
                    <a href="{{ route('attendance.index', ['date' => $date->format('Y-m-d')]) }}"
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
</div>

@endsection
