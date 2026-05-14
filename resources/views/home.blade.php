@extends('layouts.app')
@section('title', 'Trang Nhất')

@section('content')

<div class="gz-section-rule">
    <span class="gz-section-rule-text"><em>I</em> Mục Lục Số Này</span>
</div>

<div class="row g-4 align-items-stretch">
    <div class="col-md-7">
        <p class="gz-dropcap" style="font-size:1.08rem;">
            <strong>Tập I của Niên Giám Lương</strong> trình bày toàn bộ quy trình quản lý
            nhân sự, chấm công, và đặc biệt là máy tính thuế thu nhập cá nhân — căn cứ trên
            biểu thuế lũy tiến 5 bậc hiện hành. Mọi tham số được cấu hình động qua mục
            <em>Cấu Hình</em>; bảng lương sẽ tự tính lại theo công thức mới mỗi khi tham số
            thay đổi. Các con số dưới đây cập nhật tại thời điểm in.
        </p>
    </div>
    <div class="col-md-5">
        <div class="gz-card gz-card-tight">
            <div class="gz-label">Tổng nhân sự đang hoạt động</div>
            <div class="gz-figure gz-figure-accent" style="font-size:3.6rem;">
                {{ $totalEmployees }}
                <span class="gz-figure-unit">người</span>
            </div>
            <div class="gz-figure-caption">Tính đến {{ now()->format('d/m/Y') }}</div>
            <hr>
            <a href="{{ route('employees.index') }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-people"></i> Sổ Nhân Viên
            </a>
            <a href="{{ route('employees.create') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-plus"></i> Thêm Mới
            </a>
        </div>
    </div>
</div>

<div class="gz-section-rule">
    <span class="gz-section-rule-text"><em>II</em> Tra Cứu Nhanh</span>
</div>

<div class="row g-4">
    <div class="col-md-7">
        <div class="gz-card">
            <div class="gz-label mb-1">Tra cứu lương theo mã nhân viên</div>
            <p class="gz-section-lede mb-3">
                Nhập mã NV để xem phiếu lương tháng hiện tại của nhân viên đó.
            </p>
            <form action="{{ route('home.search') }}" method="POST">
                @csrf
                <div class="d-flex gap-2 align-items-end">
                    <input type="text" name="code" class="form-control form-control-lg flex-grow-1"
                           placeholder="VD: NV001" value="{{ $code }}" required>
                    <button class="btn btn-primary btn-lg" type="submit">
                        <i class="bi bi-search"></i> Tra Cứu
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-5">
        <div class="gz-card">
            <div class="gz-label mb-2">Thao tác thường ngày</div>
            <div class="d-flex flex-column gap-2">
                <a href="{{ route('attendance.index') }}" class="btn btn-outline-success">
                    <i class="bi bi-calendar-check"></i> Chấm Công Hôm Nay
                </a>
                <a href="{{ route('payroll.index') }}" class="btn btn-outline-primary">
                    <i class="bi bi-cash-stack"></i> Bảng Lương Tháng Này
                </a>
                <a href="{{ route('settings.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-sliders"></i> Cấu Hình Công Thức
                </a>
            </div>
        </div>
    </div>
</div>

<div class="gz-section-rule">
    <span class="gz-section-rule-text"><em>III</em> Biểu Thuế TNCN Hiện Hành</span>
</div>

<div class="gz-card">
    <h3 class="gz-section-title">Lũy tiến năm bậc</h3>
    <p class="gz-section-lede">
        Áp dụng cho cá nhân cư trú tại Việt Nam — thuế tính trên
        <em>thu nhập tính thuế</em> hàng tháng (sau giảm trừ gia cảnh và bảo hiểm bắt buộc).
    </p>

    <table class="gz-table">
        <thead>
            <tr>
                <th style="width:60px">Bậc</th>
                <th>Thu nhập tính thuế (VND / tháng)</th>
                <th class="num" style="width:120px">Thuế suất</th>
                <th>Công thức rút gọn</th>
            </tr>
        </thead>
        <tbody>
            <tr><td class="num">1</td><td>Đến 10.000.000</td><td class="num">5%</td><td><code>TNTT × 5%</code></td></tr>
            <tr><td class="num">2</td><td>Trên 10 – 30 triệu</td><td class="num">10%</td><td><code>TNTT × 10% − 500.000</code></td></tr>
            <tr><td class="num">3</td><td>Trên 30 – 60 triệu</td><td class="num">20%</td><td><code>TNTT × 20% − 3.500.000</code></td></tr>
            <tr><td class="num">4</td><td>Trên 60 – 100 triệu</td><td class="num">30%</td><td><code>TNTT × 30% − 9.500.000</code></td></tr>
            <tr><td class="num">5</td><td>Trên 100 triệu</td><td class="num">35%</td><td><code>TNTT × 35% − 14.500.000</code></td></tr>
        </tbody>
    </table>

    <p class="mt-3 mb-0" style="font-size:0.9rem; color:var(--gz-muted); font-style:italic;">
        * Biểu thuế trên là giá trị mặc định và có thể chỉnh sửa trong mục
        <a href="{{ route('settings.index') }}">Cấu Hình → Biểu thuế lũy tiến</a>.
    </p>
</div>

@endsection
