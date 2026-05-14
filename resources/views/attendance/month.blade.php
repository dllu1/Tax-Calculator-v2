@extends('layouts.app')
@section('title', __('Chấm công tháng').' '.$month.'/'.$year)

@push('scripts')
<style>
    @media print {
        @page { size: A4 landscape; margin: 8mm 10mm; }
        .table-responsive { overflow: visible !important; max-height: none !important; }
        .gz-grid-table { min-width: auto !important; width: 100% !important; font-size: 9pt; }
        .gz-grid-table th, .gz-grid-table td { padding: 2px 3px !important; }
        .att-cell { min-width: 0 !important; }
    }
</style>
@endpush

@section('content')

@php
    $weekDays = app()->getLocale() === 'en'
        ? ['Sun','Mon','Tue','Wed','Thu','Fri','Sat']
        : ['CN','T2','T3','T4','T5','T6','T7'];
@endphp

<div class="gz-section-rule">
    <span class="gz-section-rule-text"><em>II</em> {{ __('Toàn Cảnh Trong Tháng') }}</span>
</div>

<div class="gz-card-head" style="margin-bottom: 1rem;">
    <div>
        <h2 class="gz-section-title mb-1">{{ __('Chấm công tháng') }} {{ $month }}/{{ $year }}</h2>
        <p class="gz-section-lede mb-0">
            {{ __('Bấm vào bất kỳ ô nào để chuyển sang trang chấm công ngày tương ứng.') }}
        </p>
    </div>
    <div class="d-flex gap-2 no-print">
        <form method="GET" class="d-flex gap-2">
            <select name="month" class="form-select form-select-sm">
                @for ($m=1; $m<=12; $m++)
                    <option value="{{ $m }}" @selected($m == $month)>{{ __('Tháng') }} {{ $m }}</option>
                @endfor
            </select>
            <input type="number" name="year" class="form-control form-control-sm" value="{{ $year }}" style="width:90px">
            <button class="btn btn-sm btn-outline-primary">{{ __('Xem') }}</button>
        </form>
        <button type="button" class="btn btn-sm btn-outline-primary" onclick="window.print()">
            <i class="bi bi-file-earmark-pdf"></i> {{ __('Xuất PDF') }}
        </button>
        <a href="{{ route('attendance.index') }}" class="btn btn-sm btn-primary">
            <i class="bi bi-calendar-day"></i> {{ __('Chấm Công Hôm Nay') }}
        </a>
    </div>
</div>

<div class="gz-card">
    <div class="d-flex gap-3 flex-wrap mb-3 align-items-center" style="font-size:0.85rem;">
        <span class="gz-label">{{ __('Chú thích:') }}</span>
        <span><span class="badge att-normal" style="padding:2px 8px; border-radius:0;">N</span> {{ __('Ngày thường') }}</span>
        <span><span class="badge att-half" style="padding:2px 8px; border-radius:0;">½</span> {{ __('Nửa ngày') }}</span>
        <span><span class="badge att-sunday" style="padding:2px 8px; border-radius:0;">CN</span> {{ __('Chủ nhật (×2 công)') }}</span>
        <span><span class="badge att-leave" style="padding:2px 8px; border-radius:0;">P</span> {{ __('Có phép') }}</span>
        <span><span class="badge att-absent" style="padding:2px 8px; border-radius:0;">X</span> {{ __('Không phép') }}</span>
        <span class="text-muted"><em>{{ __('Số nhỏ ở dưới = số ca tăng ca') }}</em></span>
    </div>

    <div class="table-responsive" style="max-height: 620px; overflow:auto;">
    <table class="gz-grid-table" style="min-width: 1200px;">
        <thead class="sticky-top">
            <tr>
                <th rowspan="2" style="min-width:200px; text-align:left; padding-left:0.6rem;">{{ __('Nhân viên') }}</th>
                <th colspan="{{ $end->day }}">{{ __('Ngày trong tháng') }} {{ $month }}/{{ $year }}</th>
            </tr>
            <tr>
                @for ($d=1; $d <= $end->day; $d++)
                    @php $date = $start->copy()->day($d); @endphp
                    <th class="{{ $date->isSunday() ? 'sunday-col' : '' }}" style="min-width:42px">
                        {{ $d }}<br><small style="color:var(--gz-muted);">{{ $weekDays[$date->dayOfWeek] }}</small>
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
                        'half'    => 'att-half',
                        'sunday'  => 'att-sunday',
                        'leave'   => 'att-leave',
                        'absent'  => 'att-absent',
                        default   => '',
                    };
                    $letter = match ($att?->type) {
                        'normal' => 'N', 'half' => '½', 'sunday' => 'CN', 'leave' => 'P', 'absent' => 'X', default => '—',
                    };
                @endphp
                <td class="att-cell {{ $cls }}">
                    <a href="{{ route('attendance.index', ['date' => $date->format('Y-m-d')]) }}"
                       title="{{ __('Chấm ngày') }} {{ $date->format('d/m/Y') }}">
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
