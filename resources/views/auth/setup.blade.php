@extends('auth.layout')

@section('title', __('Tạo mật khẩu'))

@section('content')
    <h2 class="auth-title">{{ __('Tạo mật khẩu cho ứng dụng') }}</h2>
    <p class="auth-lede">
        {{ __('Lần đầu mở ứng dụng. Hãy đặt một mật khẩu để bảo vệ dữ liệu nhân sự & bảng lương.') }}
    </p>

    <form method="POST" action="{{ route('auth.setup.store') }}" autocomplete="off">
        @csrf

        <div class="mb-3">
            <label for="password" class="form-label">{{ __('Mật khẩu') }}</label>
            <input id="password" name="password" type="password" required minlength="6"
                   class="form-control @error('password') is-invalid @enderror"
                   placeholder="{{ __('Ít nhất 6 ký tự') }}" autofocus>
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-4">
            <label for="password_confirmation" class="form-label">{{ __('Nhập lại mật khẩu') }}</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required minlength="6"
                   class="form-control" placeholder="{{ __('Nhập lại để xác nhận') }}">
        </div>

        <div class="d-grid">
            <button type="submit" class="btn btn-primary">
                {{ __('Tạo mật khẩu') }}
            </button>
        </div>
    </form>
@endsection
