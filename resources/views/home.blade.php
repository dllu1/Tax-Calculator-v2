@extends('layouts.app')
@section('title', 'Trang chủ')

@section('content')
<div class="row g-3">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <h4 class="card-title"><i class="bi bi-people-fill"></i> Tổng số nhân viên</h4>
                <p class="display-5 mb-0">{{ $totalEmployees }}</p>
                <a href="{{ route('employees.index') }}" class="btn btn-outline-primary btn-sm mt-3">Quản lý NV</a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <h4 class="card-title"><i class="bi bi-search"></i> Tra cứu nhanh</h4>
                <form action="{{ route('home.search') }}" method="POST">
                    @csrf
                    <div class="input-group">
                        <input type="text" name="code" class="form-control form-control-lg"
                               placeholder="Nhập mã nhân viên..." value="{{ $code }}" required>
                        <button class="btn btn-primary" type="submit">Tra cứu</button>
                    </div>
                </form>
                <small class="text-muted">Tra cứu lương + thuế TNCN tháng hiện tại</small>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mt-3">
    <div class="card-body">
        <h5><i class="bi bi-lightning-fill"></i> Thao tác nhanh</h5>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('attendance.index') }}" class="btn btn-outline-success">
                <i class="bi bi-calendar-check"></i> Chấm công
            </a>
            <a href="{{ route('payroll.index') }}" class="btn btn-outline-info">
                <i class="bi bi-cash-stack"></i> Bảng lương tháng này
            </a>
            <a href="{{ route('employees.create') }}" class="btn btn-outline-primary">
                <i class="bi bi-person-plus"></i> Thêm nhân viên
            </a>
        </div>
    </div>
</div>

<div class="card shadow-sm mt-3">
    <div class="card-body">
        <h5><i class="bi bi-info-circle"></i> Quy định tính thuế TNCN (Biểu lũy tiến từng phần)</h5>
        <table class="table table-sm table-bordered mb-0">
            <thead class="table-light">
                <tr>
                    <th>Bậc</th><th>Thu nhập tính thuế (VND/tháng)</th><th>Thuế suất</th><th>Công thức rút gọn</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>1</td><td>Đến 10.000.000</td><td>5%</td><td>TNTT × 5%</td></tr>
                <tr><td>2</td><td>Trên 10 - 30 triệu</td><td>10%</td><td>TNTT × 10% − 500.000</td></tr>
                <tr><td>3</td><td>Trên 30 - 60 triệu</td><td>20%</td><td>TNTT × 20% − 3.500.000</td></tr>
                <tr><td>4</td><td>Trên 60 - 100 triệu</td><td>30%</td><td>TNTT × 30% − 9.500.000</td></tr>
                <tr><td>5</td><td>Trên 100 triệu</td><td>35%</td><td>TNTT × 35% − 14.500.000</td></tr>
            </tbody>
        </table>
    </div>
</div>
@endsection