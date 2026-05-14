<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingService
{
    private const CACHE_KEY = 'app.settings.all';
    private const CACHE_TTL = 3600;

    /**
     * Mặc định cho công thức tính thuế TNCN và lương.
     * Đây là fallback khi DB chưa có giá trị.
     */
    public static function defaults(): array
    {
        return [
            // === THUẾ TNCN ===
            'tax.personal_deduction' => [
                'value' => 11_000_000,
                'type' => Setting::TYPE_NUMBER,
                'group' => Setting::GROUP_TAX,
                'label' => 'Giảm trừ bản thân (VNĐ/tháng)',
                'description' => 'Mức giảm trừ gia cảnh cho người nộp thuế.',
            ],
            'tax.dependent_deduction' => [
                'value' => 4_400_000,
                'type' => Setting::TYPE_NUMBER,
                'group' => Setting::GROUP_TAX,
                'label' => 'Giảm trừ người phụ thuộc (VNĐ/người/tháng)',
                'description' => 'Mức giảm trừ cho mỗi người phụ thuộc.',
            ],
            'tax.bhxh_rate' => [
                'value' => 0.105,
                'type' => Setting::TYPE_NUMBER,
                'group' => Setting::GROUP_TAX,
                'label' => 'Tỉ lệ BHXH/BHYT/BHTN (NV đóng)',
                'description' => 'VD: 0.105 = 10.5% (BHXH 8% + BHYT 1.5% + BHTN 1%).',
            ],
            'tax.brackets' => [
                'value' => json_encode([
                    ['limit' => 10_000_000,  'rate' => 0.05, 'deduction' => 0],
                    ['limit' => 30_000_000,  'rate' => 0.10, 'deduction' => 500_000],
                    ['limit' => 60_000_000,  'rate' => 0.20, 'deduction' => 3_500_000],
                    ['limit' => 100_000_000, 'rate' => 0.30, 'deduction' => 9_500_000],
                    ['limit' => 0,           'rate' => 0.35, 'deduction' => 14_500_000],
                ]),
                'type' => Setting::TYPE_JSON,
                'group' => Setting::GROUP_TAX,
                'label' => 'Biểu thuế lũy tiến từng phần',
                'description' => 'Mỗi bậc: giới hạn (limit=0 nghĩa là không giới hạn), thuế suất, số khấu trừ.',
            ],

            // === CÔNG THỨC LƯƠNG ===
            'payroll.standard_days' => [
                'value' => 26,
                'type' => Setting::TYPE_NUMBER,
                'group' => Setting::GROUP_PAYROLL,
                'label' => 'Số công chuẩn / tháng',
                'description' => 'Dùng để tính đơn giá ngày = lương căn bản / số công chuẩn.',
            ],
            'payroll.meal_per_day' => [
                'value' => 30_000,
                'type' => Setting::TYPE_NUMBER,
                'group' => Setting::GROUP_PAYROLL,
                'label' => 'Tiền ăn / ngày công',
                'description' => 'Áp dụng cho mỗi ngày làm việc (thường + Chủ nhật).',
            ],
            'payroll.meal_per_ot_shift' => [
                'value' => 30_000,
                'type' => Setting::TYPE_NUMBER,
                'group' => Setting::GROUP_PAYROLL,
                'label' => 'Tiền ăn / ca tăng ca',
                'description' => 'Áp dụng cho mỗi ca tăng ca.',
            ],
            'payroll.sunday_multiplier' => [
                'value' => 2,
                'type' => Setting::TYPE_NUMBER,
                'group' => Setting::GROUP_PAYROLL,
                'label' => 'Hệ số công Chủ nhật',
                'description' => 'VD: 2 nghĩa là 1 ngày CN = 2 ngày công thường.',
            ],
            'payroll.overtime_multiplier' => [
                'value' => 0.5,
                'type' => Setting::TYPE_NUMBER,
                'group' => Setting::GROUP_PAYROLL,
                'label' => 'Hệ số 1 ca tăng ca (theo ngày công)',
                'description' => 'VD: 0.5 nghĩa là 1 ca TC = 0.5 ngày công.',
            ],
        ];
    }

    /**
     * Đảm bảo DB có đầy đủ các setting mặc định (tạo nếu thiếu).
     */
    public function ensureDefaults(): void
    {
        foreach (self::defaults() as $key => $config) {
            Setting::firstOrCreate(
                ['key' => $key],
                [
                    'value' => (string) $config['value'],
                    'type' => $config['type'],
                    'group' => $config['group'],
                    'label' => $config['label'],
                    'description' => $config['description'],
                ]
            );
        }
        $this->clearCache();
    }

    public function all(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            $this->ensureDefaults();
            $rows = Setting::all();
            $map = [];
            foreach ($rows as $row) {
                $map[$row->key] = [
                    'value' => $row->decoded_value,
                    'type' => $row->type,
                    'group' => $row->group,
                    'label' => $row->label,
                    'description' => $row->description,
                ];
            }
            return $map;
        });
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $all = $this->all();
        return $all[$key]['value'] ?? $default;
    }

    public function number(string $key, float $default = 0): float
    {
        $v = $this->get($key, $default);
        return is_numeric($v) ? (float) $v : $default;
    }

    public function brackets(): array
    {
        $brackets = $this->get('tax.brackets', []);
        if (!is_array($brackets)) {
            return [];
        }
        return $brackets;
    }

    public function set(string $key, mixed $value): void
    {
        $setting = Setting::where('key', $key)->first();
        if (!$setting) {
            return;
        }
        $serialized = $setting->type === Setting::TYPE_JSON
            ? json_encode($value, JSON_UNESCAPED_UNICODE)
            : (string) $value;
        $setting->update(['value' => $serialized]);
        $this->clearCache();
    }

    public function setMany(array $values): void
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value);
        }
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public function groupedForForm(): array
    {
        $this->ensureDefaults();
        return Setting::orderBy('group')->orderBy('id')->get()->groupBy('group')->toArray();
    }
}
