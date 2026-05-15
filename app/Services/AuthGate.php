<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthGate
{
    public const KEY_PASSWORD_HASH = 'auth.password_hash';
    public const KEY_RECOVERY_HASH = 'auth.recovery_code_hash';
    public const KEY_CREATED_AT    = 'auth.created_at';

    public const GROUP = 'auth';

    public const SESSION_FLAG = 'auth_passed';

    public function hasPassword(): bool
    {
        return Setting::where('key', self::KEY_PASSWORD_HASH)
            ->whereNotNull('value')
            ->where('value', '!=', '')
            ->exists();
    }

    public function verifyPassword(string $plain): bool
    {
        $hash = Setting::where('key', self::KEY_PASSWORD_HASH)->value('value');
        if (!$hash) {
            return false;
        }
        return Hash::check($plain, $hash);
    }

    public function verifyRecoveryCode(string $plain): bool
    {
        $normalized = $this->normalizeCode($plain);
        $hash = Setting::where('key', self::KEY_RECOVERY_HASH)->value('value');
        if (!$hash) {
            return false;
        }
        return Hash::check($normalized, $hash);
    }

    /**
     * Đặt mật khẩu mới và sinh recovery code mới.
     * Trả về recovery code dạng plain text (XXXX-XXXX-XXXX-XXXX) để hiển thị 1 lần.
     */
    public function setPassword(string $plain): string
    {
        $recoveryCode = $this->generateRecoveryCode();

        $this->upsert(self::KEY_PASSWORD_HASH, Hash::make($plain));
        $this->upsert(self::KEY_RECOVERY_HASH, Hash::make($this->normalizeCode($recoveryCode)));
        $this->upsert(self::KEY_CREATED_AT, now()->toIso8601String());

        return $recoveryCode;
    }

    private function generateRecoveryCode(): string
    {
        $raw = Str::upper(Str::random(16));
        return implode('-', str_split($raw, 4));
    }

    private function normalizeCode(string $code): string
    {
        return Str::upper(preg_replace('/[^A-Za-z0-9]/', '', $code));
    }

    private function upsert(string $key, string $value): void
    {
        Setting::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => Setting::TYPE_STRING,
                'group' => self::GROUP,
            ]
        );
    }
}
