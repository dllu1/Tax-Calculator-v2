<?php

namespace App\Services;

class TaxService
{
    public const PERSONAL_DEDUCTION = 11_000_000;
    public const DEPENDENT_DEDUCTION = 4_400_000;
    public const BHXH_RATE = 0.105;

    public function __construct(private readonly SettingService $settings)
    {
    }

    public function personalDeductionAmount(): float
    {
        return $this->settings->number('tax.personal_deduction', self::PERSONAL_DEDUCTION);
    }

    public function dependentDeductionAmount(): float
    {
        return $this->settings->number('tax.dependent_deduction', self::DEPENDENT_DEDUCTION);
    }

    public function bhxhRate(): float
    {
        return $this->settings->number('tax.bhxh_rate', self::BHXH_RATE);
    }

    /**
     * Biểu thuế lũy tiến (có thể cấu hình từ trang Cài đặt).
     * Bậc cuối nên có limit = 0 (không giới hạn).
     */
    public function calculatePIT(float $assessableIncome): array
    {
        if ($assessableIncome <= 0) {
            return ['tax' => 0.0, 'rate' => 0, 'deduction' => 0];
        }

        $rules = $this->settings->brackets();
        if (empty($rules)) {
            $rules = [
                ['limit' => 10_000_000,  'rate' => 0.05, 'deduction' => 0],
                ['limit' => 30_000_000,  'rate' => 0.10, 'deduction' => 500_000],
                ['limit' => 60_000_000,  'rate' => 0.20, 'deduction' => 3_500_000],
                ['limit' => 100_000_000, 'rate' => 0.30, 'deduction' => 9_500_000],
                ['limit' => 0,           'rate' => 0.35, 'deduction' => 14_500_000],
            ];
        }

        foreach ($rules as $rule) {
            $limit = (float) ($rule['limit'] ?? 0);
            $rate = (float) ($rule['rate'] ?? 0);
            $deduction = (float) ($rule['deduction'] ?? 0);
            $isLast = $limit <= 0;

            if ($isLast || $assessableIncome <= $limit) {
                $tax = $assessableIncome * $rate - $deduction;
                return [
                    'tax' => max(0.0, round($tax, 0)),
                    'rate' => $rate,
                    'deduction' => $deduction,
                ];
            }
        }

        return ['tax' => 0.0, 'rate' => 0, 'deduction' => 0];
    }

    public function personalDeduction(int $dependents = 0): float
    {
        return $this->personalDeductionAmount() + ($dependents * $this->dependentDeductionAmount());
    }

    public function bhxhAmount(float $bhxhSalary): float
    {
        return round($bhxhSalary * $this->bhxhRate(), 0);
    }
}
