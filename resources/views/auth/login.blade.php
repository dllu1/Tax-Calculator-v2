@extends('auth.layout')

@section('title', __('Đăng nhập'))

@section('content')
    <h2 class="auth-title">{{ __('Đăng nhập') }}</h2>
    <p class="auth-lede">{{ __('Nhập mật khẩu để vào ứng dụng.') }}</p>

    <form method="POST" action="{{ route('auth.login.store') }}" autocomplete="off">
        @csrf

        <div class="mb-3">
            <label for="password" class="form-label">{{ __('Mật khẩu') }}</label>
            <input id="password" name="password" type="password" required
                   class="form-control @error('password') is-invalid @enderror"
                   placeholder="{{ __('Mật khẩu của bạn') }}" autofocus>
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="d-grid mb-2">
            <button type="submit" class="btn btn-primary">
                {{ __('Đăng nhập') }}
            </button>
        </div>

        <div class="meta-link">
            <a href="{{ route('auth.forgot') }}">{{ __('Quên mật khẩu?') }}</a>
        </div>
    </form>
@endsection
