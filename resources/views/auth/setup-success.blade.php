@extends('auth.layout')

@section('title', __('Mã khôi phục'))

@section('content')
    <h2 class="auth-title">
        @if ($reason === 'reset')
            {{ __('Đã đặt lại mật khẩu') }}
        @else
            {{ __('Đã tạo mật khẩu') }}
        @endif
    </h2>
    <p class="auth-lede">
        {{ __('Đây là mã khôi phục của bạn. Hãy lưu lại ngay — bạn sẽ cần mã này nếu lỡ quên mật khẩu.') }}
    </p>

    <div class="recovery-warn">
        <strong>{{ __('Lưu ý quan trọng:') }}</strong>
        {{ __('Mã này chỉ hiển thị một lần duy nhất. Khi rời khỏi trang này, không thể xem lại.') }}
    </div>

    <label class="form-label">{{ __('Mã khôi phục') }}</label>
    <div class="recovery-code" id="recoveryCode">{{ $recoveryCode }}</div>

    <div class="d-grid gap-2 mb-2">
        <button type="button" class="btn btn-outline-secondary" id="copyBtn">
            <i class="bi bi-clipboard"></i> {{ __('Sao chép mã') }}
        </button>
    </div>

    <form method="GET" action="{{ route('home') }}">
        <div class="d-grid">
            <button type="submit" class="btn btn-primary" id="confirmBtn" disabled>
                {{ __('Tôi đã lưu mã, vào ứng dụng') }}
            </button>
        </div>
        <div class="meta-link">
            <label style="font-style: italic; color: var(--gz-muted); cursor: pointer;">
                <input type="checkbox" id="ackChk"> {{ __('Tôi xác nhận đã lưu mã khôi phục an toàn') }}
            </label>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    document.getElementById('copyBtn').addEventListener('click', async () => {
        const code = document.getElementById('recoveryCode').textContent.trim();
        try {
            await navigator.clipboard.writeText(code);
            const btn = document.getElementById('copyBtn');
            const old = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check2"></i> {{ __('Đã sao chép') }}';
            setTimeout(() => btn.innerHTML = old, 1500);
        } catch (e) {
            alert('{{ __('Không sao chép được — hãy chọn và copy thủ công.') }}');
        }
    });
    document.getElementById('ackChk').addEventListener('change', (e) => {
        document.getElementById('confirmBtn').disabled = !e.target.checked;
    });
</script>
@endpush
