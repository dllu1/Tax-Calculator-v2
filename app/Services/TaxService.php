<?php

namespace App\Services;

class TaxService
{
    public const PERSONAL_DEDUCTION = 11_000_000;
    public const DEPENDENT_DEDUCTION = 4_400_000;
    public const BHXH_RATE = 0.105;

    /**
     * 5 bậc lũy tiến từng phần - dùng công thức rút gọn (Thông tư 111/2013/TT-BTC)
     * Trả về: ['tax' => số tiền thuế, 'rate' => % bậc cao nhất, 'deduction' => khấu trừ]
     */
    public function calculatePIT(float $assessableIncome): array
    {
        if ($assessableIncome <= 0) {
            return ['tax' => 0.0, 'rate' => 0, 'deduction' => 0];
        }

        // Đơn vị: VNĐ
        $brackets = [
            ['limit' =>  10_000_000, 'rate' => 0.05, 'deduction' =>          0],
            ['limit' =>  18_000_000, 'rate' => 0.10, 'deduction' =>    250_000],
            ['limit' =>  32_000_000, 'rate' => 0.15, 'deduction' =>    750_000],
            ['limit' =>  52_000_000, 'rate' => 0.20, 'deduction' =>  1_650_000],
            ['limit' =>  80_000_000, 'rate' => 0.25, 'deduction' =>  3_250_000],
            ['limit' => 999_999_999_999, 'rate' => 0.30, 'deduction' =>  5_850_000],
            // bậc 7
            ['limit' => PHP_INT_MAX, 'rate' => 0.35, 'deduction' =>  9_850_000],
        ];

        // User dùng công thức rút gọn theo "thu nhập tính thuế" (assessable):
        //   0-10tr:   5%
        //   10-30tr:  10% - 500.000
        //   30-60tr:  20% - 3.500.000
        //   60-100tr: 30% - 9.500.000
        //   > 100tr:  35% - 14.500.000
        // -> Áp công thức này cho đúng yêu cầu của user:
        $rules = [
            ['limit' =>  10_000_000, 'rate' => 0.05, 'deduction' =>          0],
            ['limit' =>  30_000_000, 'rate' => 0.10, 'deduction' =>    500_000],
            ['limit' =>  60_000_000, 'rate' => 0.20, 'deduction' =>  3_500_000],
            ['limit' => 100_000_000, 'rate' => 0.30, 'deduction' =>  9_500_000],
            ['limit' => PHP_INT_MAX,  'rate' => 0.35, 'deduction' => 14_500_000],
        ];

        foreach ($rules as $rule) {
            if ($assessableIncome <= $rule['limit']) {
                $tax = $assessableIncome * $rule['rate'] - $rule['deduction'];
                return [
                    'tax' => max(0.0, round($tax, 0)),
                    'rate' => $rule['rate'],
                    'deduction' => $rule['deduction'],
                ];
            }
        }

        return ['tax' => 0.0, 'rate' => 0, 'deduction' => 0];
    }

    public function personalDeduction(int $dependents = 0): float
    {
        return self::PERSONAL_DEDUCTION + ($dependents * self::DEPENDENT_DEDUCTION);
    }

    public function bhxhAmount(float $bhxhSalary): float
    {
        return round($bhxhSalary * self::BHXH_RATE, 0);
    }
}