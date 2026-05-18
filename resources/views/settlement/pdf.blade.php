@extends('layouts.app')
@section('title', __('Quyết toán') . ' ' . $label)

@php
    $fmt = fn($n) => $n ? number_format((float)$n, 0, ',', '.') : '-';
    // Số âm = truy thu thuế: hiển thị trị tuyệt đối kèm class "neg" (đỏ).
    $fmtRefund = function ($n) use ($fmt) {
        if (!$n) return ['value' => '-', 'class' => ''];
        return $n > 0
            ? ['value' => $fmt($n),       'class' => '']
            : ['value' => $fmt(abs($n)),  'class' => 'neg'];
    };
    $periodCodes = [
        'q1' => 'Q01',
        'q2' => 'Q02',
        'q3' => 'Q03',
        'q4' => 'Q04',
        'year' => 'NĂM',
    ];
    $titleCode = $periodCodes[$period] . '/' . $year;
@endphp

@push('scripts')
<style>
    .pdf-settlement {
        font-family: 'Times New Roman', Times, serif;
        color: #000;
        background: #fff;
    }
    .pdf-settlement-title {
        text-align: center;
        font-weight: 700;
        font-size: 14pt;
        margin: 0 0 14pt 0;
        letter-spacing: 0.5pt;
    }
    .pdf-settlement table {
        width: 100%;
        border-collapse: collapse;
        font-size: 10pt;
    }
    .pdf-settlement th,
    .pdf-settlement td {
        border: 0.5pt solid #000;
        padding: 3pt 5pt;
        vertical-align: middle;
        background: #fff;
        color: #000;
    }
    .pdf-settlement thead th {
        font-weight: 700;
        text-align: center;
        font-style: normal;
    }
    .pdf-settlement td.num,
    .pdf-settlement th.num {
        text-align: right;
        font-variant-numeric: tabular-nums;
    }
    .pdf-settlement td.ctr,
    .pdf-settlement th.ctr {
        text-align: center;
    }
    .pdf-settlement tfoot td {
        font-weight: 700;
    }
    /* Truy thu thuế: hiển thị trị tuyệt đối + đỏ để nhận biết phần thuế còn thiếu. */
    .pdf-settlement td.neg { color: #c00; }
    .pdf-settlement-toolbar { margin-bottom: 10pt; }

    @media print {
        @page { size: A3 landscape; margin: 10mm 10mm; }
        html, body {
            background: #fff !important;
            color: #000 !important;
            font-family: 'Times New Roman', Times, serif !important;
        }
        .gz-masthead, .gz-nav, .gz-footer, .no-print, .alert,
        .gz-section-rule { display: none !important; }
        main.container, .container { max-width: 100% !important; padding: 0 !important; }
        .pdf-settlement { font-size: 9pt; }
        .pdf-settlement th, .pdf-settlement td { padding: 2pt 3pt; }
        .pdf-settlement-toolbar { display: none !important; }
        /* Strip layout color classes — chỉ giữ duy nhất .neg (đỏ) cho truy thu. */
        .pdf-settlement .text-danger,
        .pdf-settlement .text-success { color: #000 !important; }
        .pdf-settlement td.neg { color: #c00 !important; }
    }
</style>
@endpush

@section('content')

<div class="pdf-settlement-toolbar no-print d-flex justify-content-between align-items-center">
    <p class="gz-section-lede mb-0">
        {{ __('Bản in tối giản — bấm Ctrl+P để in/lưu PDF (khuyến nghị A3 landscape).') }}
    </p>
    <button type="button" class="btn btn-sm btn-primary" onclick="window.print()">
        <i class="bi bi-printer"></i> {{ __('In ngay') }}
    </button>
</div>

<div class="pdf-settlement">
    <h1 class="pdf-settlement-title">{{ __('THUẾ TNCN') }} {{ $titleCode }}</h1>

    <table>
        <thead>
            <tr>
                <th>{{ __('Họ và tên') }}</th>
                <th class="num">{{ __('Tổng thu nhập thực tế') }}</th>
                <th class="num">{{ __('BHXH 10,5%') }}</th>
                <th class="num">{{ __('Thu nhập chịu thuế TNCN có BHXH') }}</th>
                <th class="num">{{ __('Giảm trừ gia cảnh') }}</th>
                <th class="num">{{ __('Thu nhập tính thuế TNCN') }}</th>
                <th class="num">{{ __('Thuế TNCN phải nộp') }}</th>
                <th class="num">{{ __('Tiền lương còn lại sau thuế') }}</th>
                <th class="num">{{ __('Số thuế đã trừ lương') }}</th>
                <th class="num">{{ __('Số thuế phải hoàn lại') }}</th>
                <th class="ctr">{{ __('MST') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                @php $emp = $row['employee']; @endphp
                <tr>
                    <td>{{ $emp->full_name }}</td>
                    <td class="num">{{ $fmt($row['total_income']) }}</td>
                    <td class="num">{{ $fmt($row['bhxh_amount']) }}</td>
                    <td class="num">{{ $fmt($row['taxable_income']) }}</td>
                    <td class="num">{{ $fmt($row['family_deduction']) }}</td>
                    <td class="num">{{ $fmt($row['assessable_income']) }}</td>
                    <td class="num">{{ $fmt($row['pit_payable']) }}</td>
                    <td class="num">{{ $fmt($row['net_after_tax']) }}</td>
                    <td class="num">{{ $fmt($row['pit_withheld']) }}</td>
                    @php $r = $fmtRefund($row['pit_refund']); @endphp
                    <td class="num {{ $r['class'] }}">{{ $r['value'] }}</td>
                    <td class="ctr">{{ $emp->tax_code ?: '-' }}</td>
                </tr>
            @endforeach
            @if (empty($rows))
                <tr><td colspan="11" class="ctr" style="padding: 1rem;">
                    <em>{{ __('Chưa có dữ liệu quyết toán cho kỳ này') }}</em>
                </td></tr>
            @endif
        </tbody>
        <tfoot>
            <tr>
                <td>{{ __('Tổng cộng') }}</td>
                <td class="num">{{ $fmt($totals['total_income']) }}</td>
                <td class="num">{{ $fmt($totals['bhxh_amount']) }}</td>
                <td class="num">{{ $fmt($totals['taxable_income']) }}</td>
                <td class="num">{{ $fmt($totals['family_deduction']) }}</td>
                <td class="num">{{ $fmt($totals['assessable_income']) }}</td>
                <td class="num">{{ $fmt($totals['pit_payable']) }}</td>
                <td class="num">{{ $fmt($totals['net_after_tax']) }}</td>
                <td class="num">{{ $fmt($totals['pit_withheld']) }}</td>
                @php $rt = $fmtRefund($totals['pit_refund']); @endphp
                <td class="num {{ $rt['class'] }}">{{ $rt['value'] }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>

@endsection
