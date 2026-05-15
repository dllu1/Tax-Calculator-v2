@extends('auth.layout')

@section('title', __('Khôi phục mật khẩu'))

@section('content')
    <h2 class="auth-title">{{ __('Khôi phục mật khẩu') }}</h2>
    <p class="auth-lede">
        {{ __('Nhập mã khôi phục đã lưu khi tạo mật khẩu, sau đó đặt mật khẩu mới.') }}
    </p>

    <form method="POST" action="{{ route('auth.forgot.store') }}" autocomplete="off">
        @csrf

        <div class="mb-3">
            <label for="recovery_code" class="form-label">{{ __('Mã khôi phục') }}</label>
            <input id="recovery_code" name="recovery_code" type="text" required
                   class="form-control @error('recovery_code') is-invalid @enderror"
                   placeholder="XXXX-XXXX-XXXX-XXXX"
                   style="font-family: 'IBM Plex Mono', monospace; letter-spacing: 0.08em;"
                   autofocus>
            @error('recovery_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">{{ __('Mật khẩu mới') }}</label>
            <input id="password" name="password" type="password" required minlength="6"
                   class="form-control @error('password') is-invalid @enderror"
                   placeholder="{{ __('Ít nhất 6 ký tự') }}">
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-4">
            <label for="password_confirmation" class="form-label">{{ __('Nhập lại mật khẩu mới') }}</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required minlength="6"
                   class="form-control" placeholder="{{ __('Nhập lại để xác nhận') }}">
        </div>

        <div class="d-grid mb-2">
            <button type="submit" class="btn btn-primary">
                {{ __('Đặt lại mật khẩu') }}
            </button>
        </div>

        <div class="meta-link">
            <a href="{{ route('auth.login') }}">&larr; {{ __('Quay lại đăng nhập') }}</a>
        </div>
    </form>
@endsection
