@extends('layouts.app')
@section('title', 'Cấu Hình Công Thức')

@section('content')

<div class="gz-section-rule">
    <span class="gz-section-rule-text"><em>I</em> Cấu Hình Công Thức</span>
</div>

<div class="gz-card-head" style="margin-bottom: 1rem;">
    <div>
        <h2 class="gz-section-title mb-1">Tham số tính thuế &amp; lương</h2>
        <p class="gz-section-lede mb-0">
            Sửa các tham số dưới đây để áp dụng công thức mới — bảng lương sẽ tự tính lại
            theo công thức cập nhật ở lần xem tiếp theo.
        </p>
    </div>
    <form method="POST" action="{{ route('settings.reset') }}"
          onsubmit="return confirm('Khôi phục toàn bộ cấu hình về mặc định?');">
        @csrf
        <button class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-counterclockwise"></i> Khôi Phục Mặc Định
        </button>
    </form>
</div>

<ul class="nav nav-tabs" role="tablist">
    <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-tax">
            Công Thức Thuế TNCN
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-payroll">
            Công Thức Tính Lương
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-brackets">
            Biểu Thuế Lũy Tiến
        </button>
    </li>
</ul>

<div class="tab-content gz-tab-body">

    {{-- ===== TAB: THUẾ ===== --}}
    <div class="tab-pane fade show active" id="tab-tax">
        <p class="gz-section-lede mb-3">
            Các tham số giảm trừ gia cảnh và tỉ lệ bảo hiểm bắt buộc.
            Khi cập nhật, các bảng lương sẽ tự tính lại theo công thức mới.
        </p>
        <form method="POST" action="{{ route('settings.update') }}">
            @csrf @method('PUT')
            @foreach ($tax as $s)
                @if ($s->type !== 'json')
                    <div class="row align-items-center mb-3 pb-3" style="border-bottom:1px dotted var(--gz-rule);">
                        <div class="col-md-5">
                            <div class="fw-bold">{{ $s->label ?? $s->key }}</div>
                            <div class="text-muted small"><em>{{ $s->description }}</em></div>
                            <div class="mono small mt-1" style="color:var(--gz-muted);">{{ $s->key }}</div>
                        </div>
                        <div class="col-md-5">
                            <input
                                type="number"
                                step="any"
                                min="0"
                                name="settings[{{ $s->key }}]"
                                value="{{ $s->value }}"
                                class="form-control form-control-lg"
                                required>
                        </div>
                    </div>
                @endif
            @endforeach
            <div class="text-end">
                <button class="btn btn-primary"><i class="bi bi-save"></i> Lưu Cấu Hình Thuế</button>
            </div>
        </form>
    </div>

    {{-- ===== TAB: LƯƠNG ===== --}}
    <div class="tab-pane fade" id="tab-payroll">
        <p class="gz-section-lede mb-3">
            Số công chuẩn, tiền ăn, hệ số Chủ nhật, hệ số tăng ca... — quyết định cách tính lương ngày.
        </p>
        <form method="POST" action="{{ route('settings.update') }}">
            @csrf @method('PUT')
            @foreach ($payroll as $s)
                <div class="row align-items-center mb-3 pb-3" style="border-bottom:1px dotted var(--gz-rule);">
                    <div class="col-md-5">
                        <div class="fw-bold">{{ $s->label ?? $s->key }}</div>
                        <div class="text-muted small"><em>{{ $s->description }}</em></div>
                        <div class="mono small mt-1" style="color:var(--gz-muted);">{{ $s->key }}</div>
                    </div>
                    <div class="col-md-5">
                        <input
                            type="number"
                            step="any"
                            min="0"
                            name="settings[{{ $s->key }}]"
                            value="{{ $s->value }}"
                            class="form-control form-control-lg"
                            required>
                    </div>
                </div>
            @endforeach

            <div class="gz-card gz-card-tight mt-3 mb-3" style="background:var(--gz-surface-2);">
                <div class="gz-label mb-2">Công thức hiện hành</div>
                <ul class="small mb-0" style="color:var(--gz-ink-soft);">
                    <li><code>Đơn giá ngày = Lương căn bản ÷ Số công chuẩn</code></li>
                    <li><code>Lương ngày = Đơn giá ngày × (Công thường + CN × Hệ số CN)</code></li>
                    <li><code>Lương tăng ca = Đơn giá ngày × Hệ số TC × Số ca TC</code></li>
                    <li><code>Tiền ăn = Tiền ăn/ngày × (Công thường + CN) + Tiền ăn/ca TC × Số ca TC</code></li>
                    <li><code>Tổng thực nhận = Lương ngày + Lương TC + Tiền ăn + Lương SP + Chuyên cần + Phụ cấp</code></li>
                    <li><code>Còn lại = Tổng thực nhận − Tạm ứng − BHXH − Thuế TNCN</code></li>
                </ul>
            </div>

            <div class="text-end">
                <button class="btn btn-primary"><i class="bi bi-save"></i> Lưu Cấu Hình Lương</button>
            </div>
        </form>
    </div>

    {{-- ===== TAB: BIỂU THUẾ LŨY TIẾN ===== --}}
    <div class="tab-pane fade" id="tab-brackets">
        <p class="gz-section-lede mb-3">
            <strong>Biểu thuế lũy tiến từng phần.</strong> Mỗi bậc gồm:
            <em>Giới hạn trên</em> (TN tính thuế ≤ giới hạn → áp bậc này),
            <em>Thuế suất</em> (0.05 = 5%), <em>Khấu trừ</em> (số trừ trong công thức rút gọn).
            Đặt <strong>Giới hạn = 0</strong> cho bậc cuối cùng (không giới hạn).
            Công thức: <code>Thuế = TN tính thuế × Thuế suất − Khấu trừ</code>
        </p>

        <form method="POST" action="{{ route('settings.brackets.update') }}">
            @csrf @method('PUT')

            <div class="table-responsive">
                <table class="gz-table" id="brackets-table">
                    <thead>
                        <tr>
                            <th style="width:60px" class="num">Bậc</th>
                            <th>Giới hạn trên (VND) <small style="color:var(--gz-muted);"><em>— 0 = không giới hạn</em></small></th>
                            <th>Thuế suất <small style="color:var(--gz-muted);"><em>(0.05 = 5%)</em></small></th>
                            <th>Khấu trừ (VND)</th>
                            <th style="width:60px"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($brackets as $i => $b)
                            <tr>
                                <td class="num fw-bold gz-bracket-num">{{ $i + 1 }}</td>
                                <td>
                                    <input type="number" step="any" min="0"
                                           name="brackets[{{ $i }}][limit]"
                                           value="{{ $b['limit'] }}"
                                           class="form-control" required>
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0" max="1"
                                           name="brackets[{{ $i }}][rate]"
                                           value="{{ $b['rate'] }}"
                                           class="form-control" required>
                                </td>
                                <td>
                                    <input type="number" step="any" min="0"
                                           name="brackets[{{ $i }}][deduction]"
                                           value="{{ $b['deduction'] }}"
                                           class="form-control" required>
                                </td>
                                <td class="num">
                                    <button type="button" class="btn btn-sm btn-outline-danger js-remove-row">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <button type="button" id="add-bracket" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-plus"></i> Thêm Bậc Thuế
                </button>
                <button class="btn btn-primary"><i class="bi bi-save"></i> Lưu Biểu Thuế</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const table = document.querySelector('#brackets-table tbody');
    const addBtn = document.getElementById('add-bracket');

    function reindex() {
        [...table.querySelectorAll('tr')].forEach((tr, idx) => {
            tr.querySelector('.gz-bracket-num').textContent = idx + 1;
            tr.querySelectorAll('input').forEach(inp => {
                inp.name = inp.name.replace(/brackets\[\d+\]/, `brackets[${idx}]`);
            });
        });
    }

    addBtn?.addEventListener('click', () => {
        const idx = table.querySelectorAll('tr').length;
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="num fw-bold gz-bracket-num">${idx + 1}</td>
            <td><input type="number" step="any" min="0" name="brackets[${idx}][limit]" value="0" class="form-control" required></td>
            <td><input type="number" step="0.01" min="0" max="1" name="brackets[${idx}][rate]" value="0" class="form-control" required></td>
            <td><input type="number" step="any" min="0" name="brackets[${idx}][deduction]" value="0" class="form-control" required></td>
            <td class="num">
                <button type="button" class="btn btn-sm btn-outline-danger js-remove-row"><i class="bi bi-x"></i></button>
            </td>`;
        table.appendChild(tr);
    });

    table?.addEventListener('click', (e) => {
        const btn = e.target.closest('.js-remove-row');
        if (!btn) return;
        if (table.querySelectorAll('tr').length <= 1) {
            alert('Phải có ít nhất 1 bậc thuế.');
            return;
        }
        btn.closest('tr').remove();
        reindex();
    });
})();
</script>
@endpush
@endsection
