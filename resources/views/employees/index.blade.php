@extends('layouts.app')
@section('title', 'Sổ Nhân Viên')

@section('content')

<div class="gz-section-rule">
    <span class="gz-section-rule-text"><em>I</em> Sổ Nhân Sự</span>
</div>

<div class="gz-card-head" style="margin-bottom: 1rem;">
    <div>
        <h2 class="gz-section-title mb-1">Danh sách nhân viên</h2>
        <p class="gz-section-lede mb-0">
            Quản lý hồ sơ lương căn bản, bảo hiểm và người phụ thuộc.
        </p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('employees.template') }}" class="btn btn-outline-secondary">
            <i class="bi bi-file-earmark-arrow-down"></i> Tải File Mẫu
        </a>
        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#importModal">
            <i class="bi bi-file-earmark-spreadsheet"></i> Import Excel
        </button>
        <a href="{{ route('employees.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Thêm Nhân Viên
        </a>
    </div>
</div>

@if (session('import_errors'))
    <div class="alert alert-warning">
        <strong>Có {{ count(session('import_errors')) }} dòng bị bỏ qua khi import:</strong>
        <ul class="mb-0 mt-2 small">
            @foreach (session('import_errors') as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- ============ Import Modal ============ --}}
<div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius: 0; border: 1px solid var(--gz-rule); background: var(--gz-surface);">
            <form method="POST" action="{{ route('employees.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header" style="border-bottom: 1px solid var(--gz-rule);">
                    <h4 class="modal-title mb-0">Import nhân viên từ Excel</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="gz-section-lede mb-3">
                        Chọn file <code>.xlsx</code>, <code>.xls</code> hoặc <code>.csv</code> theo định dạng của
                        <a href="{{ route('employees.template') }}">file mẫu</a>. Nếu phát hiện
                        mã NV đã tồn tại, hệ thống sẽ hiện cửa sổ xác nhận để bạn chọn
                        <strong>giữ lại dữ liệu cũ</strong> hoặc <strong>ghi đè bằng dữ liệu mới</strong>.
                    </p>

                    <div class="mb-3">
                        <label class="form-label">File Excel <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control"
                               accept=".xlsx,.xls,.csv,.txt" required>
                        <small class="text-muted"><em>Tối đa 5MB.</em></small>
                    </div>

                    <div class="gz-card gz-card-tight" style="background: var(--gz-surface-2); margin-bottom: 0;">
                        <div class="gz-label mb-2">Các cột được hỗ trợ (header dòng 1)</div>
                        <table class="gz-table" style="margin-bottom: 0;">
                            <thead>
                                <tr>
                                    <th>Tên cột</th>
                                    <th>Bắt buộc</th>
                                    <th>Ghi chú</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td><code>ma_nv</code></td><td>Có</td><td>Khoá định danh, dùng để update/create</td></tr>
                                <tr><td><code>ho_va_ten</code></td><td>Có</td><td>—</td></tr>
                                <tr><td><code>chuc_vu</code></td><td>—</td><td>—</td></tr>
                                <tr><td><code>phong_ban</code></td><td>—</td><td>—</td></tr>
                                <tr><td><code>ngay_vao_lam</code></td><td>—</td><td>YYYY-MM-DD</td></tr>
                                <tr><td><code>so_nguoi_phu_thuoc</code></td><td>—</td><td>Số nguyên</td></tr>
                                <tr><td><code>luong_can_ban</code></td><td>—</td><td>VND, không cần dấu phẩy</td></tr>
                                <tr><td><code>luong_bhxh</code></td><td>—</td><td>VND</td></tr>
                                <tr><td><code>chuyen_can</code></td><td>—</td><td>VND</td></tr>
                                <tr><td><code>trang_thai</code></td><td>—</td><td>1 = Đang làm, 0 = Nghỉ</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid var(--gz-rule);">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-upload"></i> Bắt Đầu Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ============ Confirm-Duplicates Modal ============ --}}
@php $preview = session('import_preview'); @endphp
@if ($preview)
    @php $fmt = fn($n) => number_format((float) $n, 0, ',', '.'); @endphp
    <div class="modal fade" id="confirmImportModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl">
            <div class="modal-content" style="border-radius: 0; border: 1px solid var(--gz-rule); background: var(--gz-surface);">
                <form method="POST" action="{{ route('employees.import.commit') }}">
                    @csrf
                    <input type="hidden" name="key" value="{{ $preview['key'] }}">

                    <div class="modal-header" style="border-bottom: 1px solid var(--gz-rule);">
                        <h4 class="modal-title mb-0">
                            <i class="bi bi-exclamation-diamond"></i>
                            Phát hiện {{ $preview['duplicate_count'] }} nhân viên trùng mã
                        </h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <p class="gz-section-lede mb-3">
                            File Excel có <strong class="text-success">{{ $preview['new_count'] }}</strong>
                            nhân viên mới (sẽ được tạo), và
                            <strong class="text-danger">{{ $preview['duplicate_count'] }}</strong>
                            nhân viên trùng mã. Hãy chọn cách xử lý các dòng trùng:
                        </p>

                        <div class="gz-card gz-card-tight mb-3" style="background: var(--gz-surface-2);">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="action"
                                       value="skip" id="actSkip" checked>
                                <label class="form-check-label" for="actSkip">
                                    <strong>Giữ lại dữ liệu cũ</strong> — bỏ qua các dòng trùng, không thay đổi gì
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="action"
                                       value="overwrite" id="actOverwrite">
                                <label class="form-check-label" for="actOverwrite">
                                    <strong>Ghi đè bằng dữ liệu mới</strong> — cập nhật theo file Excel vừa upload
                                </label>
                            </div>
                        </div>

                        <div class="gz-label mb-2">Bảng so sánh — Hiện tại trong hệ thống ↔ Trong file Excel</div>
                        <div class="table-responsive" style="max-height: 420px;">
                            <table class="gz-table">
                                <thead style="position: sticky; top: 0; background: var(--gz-surface);">
                                    <tr>
                                        <th style="width:80px">Mã NV</th>
                                        <th>Trường</th>
                                        <th>Hiện tại</th>
                                        <th>Trong file</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach ($preview['duplicates'] as $code)
                                    @php
                                        $cur = $preview['current'][$code] ?? [];
                                        $inc = $preview['incoming'][$code] ?? [];
                                        $fields = [
                                            ['full_name', 'Họ tên', 'text'],
                                            ['position', 'Chức vụ', 'text'],
                                            ['basic_salary', 'Lương căn bản', 'money'],
                                            ['bhxh_salary', 'Lương BHXH', 'money'],
                                            ['dependents', 'NPT', 'num'],
                                            ['is_active', 'Trạng thái', 'bool'],
                                        ];
                                    @endphp
                                    @foreach ($fields as $i => $f)
                                        @php
                                            [$key, $label, $type] = $f;
                                            $curVal = $cur[$key] ?? null;
                                            $incVal = $inc[$key] ?? null;
                                            $format = function ($v) use ($type, $fmt) {
                                                if ($v === null || $v === '') return '—';
                                                if ($type === 'money') return $fmt($v);
                                                if ($type === 'bool') return $v ? 'Đang làm' : 'Nghỉ';
                                                return e($v);
                                            };
                                            $changed = (string) $curVal !== (string) $incVal;
                                        @endphp
                                        <tr style="{{ $changed ? 'background: rgba(122,31,31,0.04);' : '' }}">
                                            @if ($i === 0)
                                                <td rowspan="{{ count($fields) }}" style="vertical-align: top;">
                                                    <strong>{{ $code }}</strong>
                                                </td>
                                            @endif
                                            <td>{{ $label }}</td>
                                            <td>{{ $format($curVal) }}</td>
                                            <td class="{{ $changed ? 'text-danger fw-bold' : '' }}">
                                                {{ $format($incVal) }}
                                                @if ($changed)
                                                    <small><em>(thay đổi)</em></small>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="modal-footer" style="border-top: 1px solid var(--gz-rule);">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Hủy Toàn Bộ Import
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg"></i> Xác Nhận &amp; Tiếp Tục
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const el = document.getElementById('confirmImportModal');
            if (el) new bootstrap.Modal(el).show();
        });
    </script>
    @endpush
@endif

<div class="gz-card">
    <form method="GET" class="mb-3">
        <div class="d-flex gap-2 align-items-end">
            <div class="flex-grow-1">
                <label class="form-label">Tìm theo mã hoặc họ tên</label>
                <input name="q" class="form-control" placeholder="VD: NV001 hoặc Nguyễn..." value="{{ request('q') }}">
            </div>
            <button class="btn btn-outline-primary"><i class="bi bi-search"></i> Tìm</button>
        </div>
    </form>

    <div class="table-responsive">
    <table class="gz-table">
        <thead>
            <tr>
                <th style="width:90px">Mã NV</th>
                <th>Họ và tên</th>
                <th>Chức vụ</th>
                <th class="money">Lương căn bản</th>
                <th class="money">Lương BHXH</th>
                <th class="num" style="width:60px">NPT</th>
                <th style="width:110px">Trạng thái</th>
                <th style="width:160px"></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($employees as $e)
            <tr>
                <td><strong>{{ $e->employee_code }}</strong></td>
                <td>{{ $e->full_name }}</td>
                <td><em>{{ $e->position }}</em></td>
                <td class="money">{{ number_format($e->basic_salary, 0, ',', '.') }}</td>
                <td class="money">{{ number_format($e->bhxh_salary, 0, ',', '.') }}</td>
                <td class="num">{{ $e->dependents }}</td>
                <td>
                    @if ($e->is_active)
                        <span class="badge bg-success">Đang làm</span>
                    @else
                        <span class="badge bg-secondary">Nghỉ</span>
                    @endif
                </td>
                <td class="text-end">
                    <div class="gz-actions">
                        <a href="{{ route('payroll.show', [$e->id, now()->year, now()->month]) }}"
                           class="btn btn-sm btn-outline-info" title="Phiếu lương">
                            <i class="bi bi-receipt"></i>
                        </a>
                        <a href="{{ route('employees.edit', $e) }}" class="btn btn-sm btn-outline-warning" title="Sửa">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('employees.destroy', $e) }}" method="POST"
                              onsubmit="return confirm('Xóa nhân viên {{ $e->full_name }}?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" title="Xóa"><i class="bi bi-trash"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="8" class="text-center" style="color:var(--gz-muted); padding:2rem;">
                <em>Chưa có nhân viên nào trong sổ.</em>
            </td></tr>
            @endforelse
        </tbody>
    </table>
    </div>

    <div class="mt-3">
        {{ $employees->links() }}
    </div>
</div>

@endsection
