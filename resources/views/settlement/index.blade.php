@extends('layouts.app')
@section('title', __('Quyết Toán Thuế TNCN'))

@php
    $periodInfo = [
        'q1'   => ['label' => __('Quý 1'),  'range' => __('Tháng 12 năm trước → Tháng 2')],
        'q2'   => ['label' => __('Quý 2'),  'range' => __('Tháng 3 → Tháng 5')],
        'q3'   => ['label' => __('Quý 3'),  'range' => __('Tháng 6 → Tháng 8')],
        'q4'   => ['label' => __('Quý 4'),  'range' => __('Tháng 9 → Tháng 11')],
        'year' => ['label' => __('Cả năm'), 'range' => __('Tháng 12 năm trước → Tháng 11')],
    ];
@endphp

@section('content')

<div class="gz-section-rule">
    <span class="gz-section-rule-text"><em>I</em> {{ __('Quyết Toán Thuế TNCN') }}</span>
</div>

<div class="gz-card-head" style="margin-bottom: 1rem;">
    <div>
        <h2 class="gz-section-title mb-1">{{ __('Quyết toán năm') }} {{ $year }}</h2>
        <p class="gz-section-lede mb-0">
            {{ __('Tổng hợp thu nhập & thuế TNCN theo quý hoặc cả năm. Mỗi mục có thể in/lưu PDF.') }}
        </p>
    </div>
    <form method="GET" class="d-flex gap-2 align-items-end no-print">
        <div>
            <label class="form-label">{{ __('Năm quyết toán') }}</label>
            <input type="number" name="year" class="form-control form-control-sm" value="{{ $year }}" style="width:110px">
        </div>
        <button class="btn btn-sm btn-outline-primary">{{ __('Xem') }}</button>
    </form>
</div>

<div class="row g-3">
    @foreach ($periods as $p)
        @php $info = $periodInfo[$p]; @endphp
        <div class="col-md-4">
            <div class="gz-card gz-card-tight h-100 d-flex flex-column">
                <div class="gz-label">{{ $info['label'] }}</div>
                <div class="gz-figure-sm mb-1">
                    @if ($p === 'year')
                        {{ $year }}
                    @else
                        {{ strtoupper($p) }}/{{ $year }}
                    @endif
                </div>
                <div class="gz-figure-caption mb-3">{{ $info['range'] }}</div>
                <div class="mt-auto d-flex gap-2 flex-wrap">
                    <a href="{{ route('settlement.show', ['period' => $p, 'year' => $year]) }}"
                       class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-eye"></i> {{ __('Xem chi tiết') }}
                    </a>
                    <button type="button" class="btn btn-sm btn-outline-secondary"
                            onclick="exportPdf('settlement', {year: {{ $year }}, period: '{{ $p }}'})">
                        <i class="bi bi-file-earmark-pdf"></i> {{ __('Xuất PDF') }}
                    </button>
                </div>
            </div>
        </div>
    @endforeach
</div>

@endsection
