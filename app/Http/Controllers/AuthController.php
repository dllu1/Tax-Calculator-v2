<?php

namespace App\Http\Controllers;

use App\Services\AuthGate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(private readonly AuthGate $gate)
    {
    }

    public function showSetup()
    {
        if ($this->gate->hasPassword()) {
            return redirect()->route('home');
        }
        return view('auth.setup');
    }

    public function storeSetup(Request $request)
    {
        if ($this->gate->hasPassword()) {
            return redirect()->route('home');
        }

        $request->validate([
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $recoveryCode = $this->gate->setPassword($request->input('password'));

        $request->session()->put(AuthGate::SESSION_FLAG, true);
        $request->session()->regenerate();

        return redirect()->route('auth.recovery-display')
            ->with('recovery_code', $recoveryCode)
            ->with('recovery_reason', 'setup');
    }

    public function showRecoveryDisplay(Request $request)
    {
        $code = $request->session()->get('recovery_code');
        $reason = $request->session()->get('recovery_reason', 'setup');
        if (!$code) {
            return redirect()->route('home');
        }
        return view('auth.setup-success', [
            'recoveryCode' => $code,
            'reason' => $reason,
        ]);
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        $key = 'auth-login|' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'password' => __('Quá nhiều lần thử. Vui lòng đợi :seconds giây.', ['seconds' => $seconds]),
            ]);
        }

        if (!$this->gate->verifyPassword($request->input('password'))) {
            RateLimiter::hit($key, 60);
            throw ValidationException::withMessages([
                'password' => __('Mật khẩu không đúng.'),
            ]);
        }

        RateLimiter::clear($key);
        $request->session()->put(AuthGate::SESSION_FLAG, true);
        $request->session()->regenerate();

        return redirect()->intended(route('home'));
    }

    public function logout(Request $request)
    {
        $request->session()->forget(AuthGate::SESSION_FLAG);
        $request->session()->regenerate();
        return redirect()->route('auth.login')->with('success', __('Đã đăng xuất.'));
    }

    public function showForgot()
    {
        return view('auth.forgot');
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'recovery_code' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $key = 'auth-recovery|' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'recovery_code' => __('Quá nhiều lần thử. Vui lòng đợi :seconds giây.', ['seconds' => $seconds]),
            ]);
        }

        if (!$this->gate->verifyRecoveryCode($request->input('recovery_code'))) {
            RateLimiter::hit($key, 300);
            throw ValidationException::withMessages([
                'recovery_code' => __('Mã khôi phục không đúng.'),
            ]);
        }

        RateLimiter::clear($key);
        $recoveryCode = $this->gate->setPassword($request->input('password'));

        $request->session()->put(AuthGate::SESSION_FLAG, true);
        $request->session()->regenerate();

        return redirect()->route('auth.recovery-display')
            ->with('recovery_code', $recoveryCode)
            ->with('recovery_reason', 'reset');
    }
}
