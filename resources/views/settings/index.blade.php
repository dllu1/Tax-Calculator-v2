@extends('layouts.app')
@section('title', 'Cấu hình công thức tính')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-sliders"></i> Cấu hình công thức tính thuế &amp; lương</h4>
    <form method="POST" action="{{ route('settings.reset') }}"
          onsubmit="return confirm('Khôi phục toàn bộ cấu hình về mặc định?');">
        @csrf
        <button class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-counterclockwise"></i> Khôi phục mặc định
        </button>
    </form>
</div>

<ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-tax">
            <i class="bi bi-percent"></i> Công thức thuế TNCN
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-payroll">
            <i class="bi bi-cash-coin"></i> Công thức tính lương
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-brackets">
            <i class="bi bi-bar-chart-steps"></i> Biểu thuế lũy tiến
        </button>
    </li>
</ul>

<div class="tab-content">
    {{-- ===== TAB: THUẾ ===== --}}
    <div class="tab-pane fade show active" id="tab-tax">
        <form method="POST" action="{{ route('settings.update') }}">
            @csrf @method('PUT')
            <div class="card shadow-sm">
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        Các tham số dùng cho công thức tính <strong>thuế TNCN</strong>.
                        Khi cập nhật, các bảng lương sẽ tự tính lại theo công thức mới ở lần xem tiếp theo.
                    </p>
                    @foreach ($tax as $s)
                        @if ($s->type !== 'json')
                            <div class="mb-3 row align-items-center">
                                <label class="col-md-4 col-form-label">
                                    <strong>{{ $s->label ?? $s->key }}</strong>
                                    <div class="text-muted small">{{ $s->description }}</div>
                                </label>
                                <div class="col-md-4">
                                    <input
                                        type="number"
                                        step="any"
                                        min="0"
                                        name="settings[{{ $s->key }}]"
                                        value="{{ $s->value }}"
                                        class="form-control"
                                        required>
                                </div>
                                <div class="col-md-4 text-muted small">
                                    <code>{{ $s->key }}</code>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
                <div class="card-footer text-end">
                    <button class="btn btn-primary"><i class="bi bi-save"></i> Lưu cấu hình thuế</button>
                </div>
            </div>
        </form>
    </div>

    {{-- ===== TAB: LƯƠNG ===== --}}
    <div class="tab-pane fade" id="tab-payroll">
        <form method="POST" action="{{ route('settings.update') }}">
            @csrf @method('PUT')
            <div class="card shadow-sm">
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        Các tham số dùng cho công thức tính <strong>lương</strong>: số công chuẩn,
                        tiền ăn, hệ số Chủ nhật, hệ số tăng ca...
                    </p>
                    @foreach ($payroll as $s)
                        <div class="mb-3 row align-items-center">
                            <label class="col-md-4 col-form-label">
                                <strong>{{ $s->label ?? $s->key }}</strong>
                                <div class="text-muted small">{{ $s->description }}</div>
                            </label>
                            <div class="col-md-4">
                                <input
                                    type="number"
                                    step="any"
                                    min="0"
                                    name="settings[{{ $s->key }}]"
                                    value="{{ $s->value }}"
                                    class="form-control"
                                    required>
                            </div>
                            <div class="col-md-4 text-muted small">
                                <code>{{ $s->key }}</code>
                            </div>
                        </div>
                    @endforeach

                    <hr>
                    <h6 class="text-muted">Công thức hiện hành</h6>
                    <ul class="small text-muted mb-0">
                        <li><code>Đơn giá ngày = Lương căn bản ÷ Số công chuẩn</code></li>
                        <li><code>Lương ngày = Đơn giá ngày × (Công thường + CN × Hệ số CN)</code></li>
                        <li><code>Lương tăng ca = Đơn giá ngày × Hệ số TC × Số ca TC</code></li>
                        <li><code>Tiền ăn = Tiền ăn/ngày × (Công thường + CN) + Tiền ăn/ca TC × Số ca TC</code></li>
                        <li><code>Tổng thực nhận = Lương ngày + Lương TC + Tiền ăn + Lương SP + Chuyên cần + Phụ cấp</code></li>
                        <li><code>Còn lại = Tổng thực nhận − Tạm ứng − BHXH − Thuế TNCN</code></li>
                    </ul>
                </div>
                <div class="card-footer text-end">
                    <button class="btn btn-primary"><i class="bi bi-save"></i> Lưu cấu hình lương</button>
                </div>
            </div>
        </form>
    </div>

    {{-- ===== TAB: BIỂU THUẾ LŨY TIẾN ===== --}}
    <div class="tab-pane fade" id="tab-brackets">
        <form method="POST" action="{{ route('settings.brackets.update') }}">
            @csrf @method('PUT')
            <div class="card shadow-sm">
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        <strong>Biểu thuế lũy tiến từng phần.</strong> Mỗi bậc gồm:
                        <em>Giới hạn trên</em> (TN tính thuế ≤ giới hạn → áp bậc này),
                        <em>Thuế suất</em> (dạng thập phân: 0.05 = 5%),
                        <em>Khấu trừ</em> (số tiền trừ trong công thức rút gọn).
                        Đặt <strong>Giới hạn = 0</strong> cho bậc cuối cùng (không giới hạn).
                        <br>
                        Công thức: <code>Thuế = TN tính thuế × Thuế suất − Khấu trừ</code>
                    </p>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle" id="brackets-table">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:60px">Bậc</th>
                                    <th>Giới hạn trên (VNĐ) <small class="text-muted">— 0 = không giới hạn</small></th>
                                    <th>Thuế suất <small class="text-muted">(0.05 = 5%)</small></th>
                                    <th>Khấu trừ (VNĐ)</th>
                                    <th style="width:80px"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($brackets as $i => $b)
                                    <tr>
                                        <td class="text-center fw-bold">{{ $i + 1 }}</td>
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
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-danger js-remove-row">
                                                <i class="bi bi-x"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <button type="button" id="add-bracket" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-plus"></i> Thêm bậc thuế
                    </button>
                </div>
                <div class="card-footer text-end">
                    <button class="btn btn-primary"><i class="bi bi-save"></i> Lưu biểu thuế</button>
                </div>
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
            tr.querySelector('td.fw-bold').textContent = idx + 1;
            tr.querySelectorAll('input').forEach(inp => {
                inp.name = inp.name.replace(/brackets\[\d+\]/, `brackets[${idx}]`);
            });
        });
    }

    addBtn?.addEventListener('click', () => {
        const idx = table.querySelectorAll('tr').length;
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="text-center fw-bold">${idx + 1}</td>
            <td><input type="number" step="any" min="0" name="brackets[${idx}][limit]" value="0" class="form-control" required></td>
            <td><input type="number" step="0.01" min="0" max="1" name="brackets[${idx}][rate]" value="0" class="form-control" required></td>
            <td><input type="number" step="any" min="0" name="brackets[${idx}][deduction]" value="0" class="form-control" required></td>
            <td class="text-center">
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
