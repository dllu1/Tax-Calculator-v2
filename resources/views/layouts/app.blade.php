<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Tính thuế TNCN') - {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #f5f7fb; }
        .navbar-brand { font-weight: 700; }
        .table-sm td, .table-sm th { padding: .35rem .5rem; }
        .money { text-align: right; font-variant-numeric: tabular-nums; }
        .badge-type-normal  { background: #198754; }
        .badge-type-sunday  { background: #fd7e14; }
        .badge-type-absent  { background: #dc3545; }
        .badge-type-leave   { background: #6c757d; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-3">
    <div class="container">
        <a class="navbar-brand" href="{{ route('home') }}">
            <i class="bi bi-calculator-fill"></i> Tính thuế TNCN
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nv">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="nv">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="{{ route('employees.index') }}">Nhân viên</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('attendance.index') }}">Chấm công</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('payroll.index') }}">Bảng lương</a></li>
            </ul>
            <form action="{{ route('home.search') }}" method="POST" class="d-flex">
                @csrf
                <input class="form-control me-2" name="code" placeholder="Nhập mã NV..." required>
                <button class="btn btn-light" type="submit"><i class="bi bi-search"></i></button>
            </form>
        </div>
    </div>
</nav>

<div class="container">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $err) <li>{{ $err }}</li> @endforeach
            </ul>
        </div>
    @endif

    @yield('content')
</div>

<footer class="text-center text-muted mt-5 mb-3">
    <small>© {{ date('Y') }} - Tính thuế TNCN - Laravel {{ app()->version() }}</small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>