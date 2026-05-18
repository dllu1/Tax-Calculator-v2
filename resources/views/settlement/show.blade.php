@extends('layouts.app')
@section('title', __('Quyết toán') . ' ' . $label)

@php
    $fmt = fn($n) => $n ? number_format((float)$n, 0, ',', '.') : '-';
    // Số thuế hoàn/truy thu: hoàn (>0) hiển thị xanh; truy thu (<0) hiển thị
    // GIÁ TRỊ TUYỆT ĐỐI bằng màu đỏ để dễ nhận biết khoản thuế đang bị thiếu.
    $fmtRefund = function ($n) use ($fmt) {
        if (!$n) return ['value' => '-', 'class' => ''];
        return $n > 0
            ? ['value' => $fmt($n),        'class' => 'text-success']
            : ['value' => $fmt(abs($n)),   'class' => 'text-danger'];
    };
    $periodLabels = [
        'q1' => __('Quý 1'),
        'q2' => __('Quý 2'),
        'q3' => __('Quý 3'),
        'q4' => __('Quý 4'),
        'year' => __('Cả năm'),
    ];
    $title = $periodLabels[$period] . ' · ' . $label;
@endphp

@section('content')

<div class="gz-section-rule">
    <span class="gz-section-rule-text"><em>I</em> {{ __('Quyết Toán Thuế TNCN') }} — {{ $title }}</span>
</div>

<div class="gz-card-head" style="margin-bottom: 1rem;">
    <div>
        <h2 class="gz-section-title mb-1">{{ __('Thuế TNCN') }} {{ strtoupper($period === 'year' ? 'NĂM' : $period) }}/{{ $year }}</h2>
        <p class="gz-section-lede mb-0">
            {{ __('Từ') }} {{ $start->format('m/Y') }} {{ __('đến') }} {{ $end->format('m/Y') }} ·
            {{ count($rows) }} {{ __('nhân viên') }}
        </p>
    </div>
    <div class="d-flex gap-2 align-items-end no-print">
        <a href="{{ route('settlement.index', ['year' => $year]) }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> {{ __('Quay lại') }}
        </a>
        <button type="button" class="btn btn-sm btn-primary"
                onclick="exportPdf('settlement', {year: {{ $year }}, period: '{{ $period }}'})">
            <i class="bi bi-file-earmark-pdf"></i> {{ __('Xuất PDF') }}
        </button>
    </div>
</div>

<div class="gz-card">
    <div class="table-responsive payroll-scroll" style="max-height: 70vh; overflow-y: auto;">
    <table class="gz-table payroll-table-sticky">
        <thead>
            <tr>
                <th>{{ __('Họ và tên') }}</th>
                <th class="money">{{ __('Tổng thu nhập thực tế') }}</th>
                <th class="money">{{ __('BHXH 10,5%') }}</th>
                <th class="money">{{ __('Thu nhập chịu thuế TNCN có BHXH') }}</th>
                <th class="money">{{ __('Giảm trừ gia cảnh') }}</th>
                <th class="money">{{ __('Thu nhập tính thuế TNCN') }}</th>
                <th class="money">{{ __('Thuế TNCN phải nộp') }}</th>
                <th class="money">{{ __('Tiền lương còn lại sau thuế') }}</th>
                <th class="money">{{ __('Số thuế đã trừ lương') }}</th>
                <th class="money">{{ __('Số thuế phải hoàn lại') }}</th>
                <th>{{ __('MST') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                @php $emp = $row['employee']; @endphp
                <tr>
                    <td>{{ $emp->full_name }}</td>
                    <td class="money">{{ $fmt($row['total_income']) }}</td>
                    <td class="money">{{ $fmt($row['bhxh_amount']) }}</td>
                    <td class="money">{{ $fmt($row['taxable_income']) }}</td>
                    <td class="money">{{ $fmt($row['family_deduction']) }}</td>
                    <td class="money">{{ $fmt($row['assessable_income']) }}</td>
                    <td class="money text-danger">{{ $fmt($row['pit_payable']) }}</td>
                    <td class="money">{{ $fmt($row['net_after_tax']) }}</td>
                    <td class="money">{{ $fmt($row['pit_withheld']) }}</td>
                    @php $r = $fmtRefund($row['pit_refund']); @endphp
                    <td class="money {{ $r['class'] }}">{{ $r['value'] }}</td>
                    <td>{{ $emp->tax_code ?: '-' }}</td>
                </tr>
            @endforeach
            @if (empty($rows))
                <tr><td colspan="11" class="text-center" style="color:var(--gz-muted); padding:2rem;">
                    <em>{{ __('Chưa có dữ liệu quyết toán cho kỳ này') }}</em>
                </td></tr>
            @endif
        </tbody>
        <tfoot>
            <tr>
                <td>{{ __('Tổng cộng') }}</td>
                <td class="money">{{ $fmt($totals['total_income']) }}</td>
                <td class="money">{{ $fmt($totals['bhxh_amount']) }}</td>
                <td class="money">{{ $fmt($totals['taxable_income']) }}</td>
                <td class="money">{{ $fmt($totals['family_deduction']) }}</td>
                <td class="money">{{ $fmt($totals['assessable_income']) }}</td>
                <td class="money text-danger">{{ $fmt($totals['pit_payable']) }}</td>
                <td class="money">{{ $fmt($totals['net_after_tax']) }}</td>
                <td class="money">{{ $fmt($totals['pit_withheld']) }}</td>
                @php $rt = $fmtRefund($totals['pit_refund']); @endphp
                <td class="money {{ $rt['class'] }}">{{ $rt['value'] }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
    </div>
</div>

@endsection
