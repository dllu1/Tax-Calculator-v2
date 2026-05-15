@extends('layouts.app')
@section('title', __('Bảng lương').' '.$month.'/'.$year)

@php $fmt = fn($n) => number_format($n, 0, ',', '.'); @endphp

@section('content')

<div class="gz-section-rule">
    <span class="gz-section-rule-text"><em>I</em> {{ __('Bản Tổng Kê Tháng') }} {{ $month }}/{{ $year }}</span>
</div>

<div class="gz-card-head" style="margin-bottom: 1rem;">
    <div>
        <h2 class="gz-section-title mb-1">{{ __('Bảng lương toàn công ty') }}</h2>
        <p class="gz-section-lede mb-0">
            {{ __('Số liệu được tính lại tự động mỗi lần truy cập theo công thức hiện hành trong mục') }}
            <a href="{{ route('settings.index') }}">{{ __('Cấu Hình') }}</a>.
        </p>
    </div>
    <div class="d-flex gap-2 align-items-end no-print">
        <form method="GET" class="d-flex gap-2 align-items-end">
            <select name="month" class="form-select form-select-sm">
                @for ($m=1; $m<=12; $m++)
                    <option value="{{ $m }}" @selected($m == $month)>{{ __('Tháng') }} {{ $m }}</option>
                @endfor
            </select>
            <input type="number" name="year" class="form-control form-control-sm" value="{{ $year }}" style="width:90px">
            <button class="btn btn-sm btn-outline-primary">{{ __('Tính Lại') }}</button>
        </form>
        <button type="button" class="btn btn-sm btn-primary" onclick="window.print()">
            <i class="bi bi-file-earmark-pdf"></i> {{ __('Xuất PDF') }}
        </button>
    </div>
</div>

{{-- Tổng quan: 3 figure ngang --}}
<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="gz-card gz-card-tight">
            <div class="gz-label">{{ __('Tổng thực nhận') }}</div>
            <div class="gz-figure-sm">{{ $fmt($payrolls->sum('total_income')) }}
                <span class="gz-figure-unit">₫</span></div>
            <div class="gz-figure-caption">{{ $payrolls->count() }} {{ __('nhân viên') }} · {{ __('trước trừ') }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="gz-card gz-card-tight">
            <div class="gz-label">{{ __('Tổng thuế TNCN') }}</div>
            <div class="gz-figure-sm gz-figure-accent">{{ $fmt($payrolls->sum('pit_amount')) }}
                <span class="gz-figure-unit">₫</span></div>
            <div class="gz-figure-caption">{{ __('Lũy tiến năm bậc') }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="gz-card gz-card-tight">
            <div class="gz-label">{{ __('Tổng lương còn lại') }}</div>
            <div class="gz-figure-sm gz-figure-success">{{ $fmt($payrolls->sum('net_salary')) }}
                <span class="gz-figure-unit">₫</span></div>
            <div class="gz-figure-caption">{{ __('Sau khấu trừ & tạm ứng') }}</div>
        </div>
    </div>
</div>

<div class="gz-card">
    <div class="table-responsive payroll-scroll" style="max-height: 65vh; overflow-y: auto;">
    <table class="gz-table payroll-table-sticky">
        <thead>
            <tr>
                <th style="width:80px">{{ __('Mã NV') }}</th>
                <th>{{ __('Họ tên') }}</th>
                <th class="num" style="width:60px">{{ __('Thường') }}</th>
                <th class="num" style="width:50px">{{ __('CN') }}</th>
                <th class="num" style="width:50px">{{ __('TC') }}</th>
                <th class="money">{{ __('Tổng thực nhận') }}</th>
                <th class="money">{{ __('BHXH') }}</th>
                <th class="money">{{ __('Thuế TNCN') }}</th>
                <th class="money">{{ __('Tạm ứng') }}</th>
                <th class="money">{{ __('Còn lại') }}</th>
                <th style="width:42px"></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($payrolls as $p)
            <tr>
                <td><strong>{{ $p->employee->employee_code }}</strong></td>
                <td>{{ $p->employee->full_name }}</td>
                <td class="num">{{ $p->normal_days }}</td>
                <td class="num">{{ $p->sunday_days }}</td>
                <td class="num">{{ $p->overtime_shifts }}</td>
                <td class="money">{{ $fmt($p->total_income) }}</td>
                <td class="money">{{ $fmt($p->bhxh_amount) }}</td>
                <td class="money text-danger">{{ $fmt($p->pit_amount) }}</td>
                <td class="money">{{ $fmt($p->advance) }}</td>
                <td class="money fw-bold text-success">{{ $fmt($p->net_salary) }}</td>
                <td>
                    <div class="gz-actions">
                        <a href="{{ route('payroll.show', [$p->employee_id, $year, $month]) }}"
                           class="btn btn-sm btn-outline-info" title="{{ __('Xem phiếu lương') }}">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="{{ route('payroll.show', [$p->employee_id, $year, $month]) }}?print=1"
                           target="_blank"
                           class="btn btn-sm btn-outline-primary" title="{{ __('Xuất PDF') }}">
                            <i class="bi bi-file-earmark-pdf"></i>
                        </a>
                    </div>
                </td>
            </tr>
            @endforeach
            @if ($payrolls->isEmpty())
            <tr><td colspan="11" class="text-center" style="color:var(--gz-muted); padding:2rem;">
                <em>{{ __('Chưa có nhân viên để tính lương') }}</em>
            </td></tr>
            @endif
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="text-end">{{ __('TỔNG CỘNG') }}</td>
                <td class="money">{{ $fmt($payrolls->sum('total_income')) }}</td>
                <td class="money">{{ $fmt($payrolls->sum('bhxh_amount')) }}</td>
                <td class="money text-danger">{{ $fmt($payrolls->sum('pit_amount')) }}</td>
                <td class="money">{{ $fmt($payrolls->sum('advance')) }}</td>
                <td class="money text-success">{{ $fmt($payrolls->sum('net_salary')) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
    </div>
</div>

@endsection
