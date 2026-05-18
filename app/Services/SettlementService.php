<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Payroll;
use Carbon\Carbon;

/**
 * Quyết toán thuế TNCN — gom dữ liệu Payroll theo quý hoặc cả năm
 * và tính lại "Thuế TNCN phải nộp" theo biểu lũy tiến trên tổng kỳ.
 *
 * Quý tài chính (theo yêu cầu user):
 *   Q1: Tháng 12 (năm trước) → Tháng 2 (năm này)
 *   Q2: Tháng 3 → Tháng 5
 *   Q3: Tháng 6 → Tháng 8
 *   Q4: Tháng 9 → Tháng 11
 *   Year: Tháng 12 (năm trước) → Tháng 11 (năm này)
 */
class SettlementService
{
    public const PERIODS = ['q1', 'q2', 'q3', 'q4', 'year'];

    public function __construct(
        private readonly TaxService $tax,
        private readonly PayrollService $payroll,
    ) {
    }

    /**
     * Trả về danh sách (year, month) cho 1 kỳ — cho phép payroll service
     * tính lại từng tháng nếu chưa có record.
     *
     * @return array<int, array{year:int, month:int}>
     */
    public function periodMonths(string $period, int $year): array
    {
        return match ($period) {
            'q1'   => [
                ['year' => $year - 1, 'month' => 12],
                ['year' => $year,     'month' => 1],
                ['year' => $year,     'month' => 2],
            ],
            'q2'   => [
                ['year' => $year, 'month' => 3],
                ['year' => $year, 'month' => 4],
                ['year' => $year, 'month' => 5],
            ],
            'q3'   => [
                ['year' => $year, 'month' => 6],
                ['year' => $year, 'month' => 7],
                ['year' => $year, 'month' => 8],
            ],
            'q4'   => [
                ['year' => $year, 'month' => 9],
                ['year' => $year, 'month' => 10],
                ['year' => $year, 'month' => 11],
            ],
            'year' => array_merge(
                [['year' => $year - 1, 'month' => 12]],
                array_map(fn ($m) => ['year' => $year, 'month' => $m], range(1, 11)),
            ),
            default => throw new \InvalidArgumentException("Unknown settlement period: {$period}"),
        };
    }

    public function periodLabel(string $period, int $year): string
    {
        return match ($period) {
            'q1', 'q2', 'q3', 'q4' => strtoupper($period) . '/' . $year,
            'year' => (string) $year,
            default => '',
        };
    }

    public function periodRange(string $period, int $year): array
    {
        $months = $this->periodMonths($period, $year);
        $first = $months[0];
        $last = $months[count($months) - 1];
        return [
            'start' => Carbon::create($first['year'], $first['month'], 1)->startOfMonth(),
            'end'   => Carbon::create($last['year'], $last['month'], 1)->endOfMonth(),
        ];
    }

    /**
     * Build dữ liệu quyết toán cho 1 kỳ.
     *
     * @return array{
     *   period: string,
     *   year: int,
     *   label: string,
     *   start: \Carbon\Carbon,
     *   end: \Carbon\Carbon,
     *   rows: array<int, array<string, mixed>>,
     *   totals: array<string, float>,
     * }
     */
    public function build(string $period, int $year): array
    {
        $months = $this->periodMonths($period, $year);
        $range = $this->periodRange($period, $year);

        $employees = Employee::orderBy('employee_code')->get();

        $rows = [];
        $totals = array_fill_keys([
            'total_income', 'bhxh_amount', 'taxable_income', 'family_deduction',
            'assessable_income', 'pit_payable', 'net_after_tax',
            'pit_withheld', 'pit_refund',
        ], 0.0);

        foreach ($employees as $emp) {
            $sumTotalIncome = 0.0;
            $sumBhxh = 0.0;
            $sumTaxableIncome = 0.0;
            $sumFamilyDeduction = 0.0;
            $sumNetSalary = 0.0;
            $sumPitWithheld = 0.0;
            $hasAnyPayroll = false;

            foreach ($months as $ym) {
                // NV đang hoạt động: LUÔN gọi calculate() để tự cập nhật theo
                // settings hiện hành (giảm trừ gia cảnh, tỉ lệ BHXH, biểu thuế…).
                // Nếu chỉ đọc Payroll record sẵn có, các thay đổi cấu hình sẽ
                // không phản ánh và dẫn đến sai số thuế ở quyết toán.
                // NV đã nghỉ: chỉ đọc record có sẵn, không tạo mới.
                if ($emp->is_active) {
                    $payroll = $this->payroll->calculate($emp, $ym['year'], $ym['month']);
                } else {
                    $payroll = Payroll::where([
                        'employee_id' => $emp->id,
                        'year' => $ym['year'],
                        'month' => $ym['month'],
                    ])->first();
                }

                if (!$payroll) {
                    continue;
                }
                $hasAnyPayroll = true;

                $sumTotalIncome    += (float) $payroll->total_income;
                $sumBhxh           += (float) $payroll->bhxh_amount;
                $sumTaxableIncome  += (float) $payroll->taxable_income;
                $sumFamilyDeduction += (float) $payroll->personal_deduction
                                     + (float) $payroll->dependent_deduction;
                $sumNetSalary      += (float) $payroll->net_salary;
                $sumPitWithheld    += (float) $payroll->pit_amount;
            }

            // Bỏ qua nhân viên không có dữ liệu trong kỳ (vd. NV đã nghỉ và chưa từng tính lương).
            if (!$hasAnyPayroll) {
                continue;
            }

            // "Thu nhập tính thuế TNCN" = "Thu nhập chịu thuế TNCN có BHXH" - BHXH - giảm trừ
            $assessableIncome = max(0.0, round($sumTaxableIncome - $sumBhxh - $sumFamilyDeduction, 0));

            // "Thuế TNCN phải nộp" — biểu lũy tiến áp lên tổng kỳ.
            $pit = $this->tax->calculatePIT($assessableIncome);
            $pitPayable = (float) $pit['tax'];

            // "Số thuế phải hoàn lại" = đã trừ - phải nộp (chỉ hoàn nếu > 0)
            $refund = round($sumPitWithheld - $pitPayable, 0);

            $rows[] = [
                'employee' => $emp,
                'total_income'     => round($sumTotalIncome, 0),
                'bhxh_amount'      => round($sumBhxh, 0),
                'taxable_income'   => round($sumTaxableIncome, 0),
                'family_deduction' => round($sumFamilyDeduction, 0),
                'assessable_income' => $assessableIncome,
                'pit_payable'      => $pitPayable,
                'net_after_tax'    => round($sumNetSalary, 0),
                'pit_withheld'     => round($sumPitWithheld, 0),
                'pit_refund'       => $refund,
            ];

            $totals['total_income']     += round($sumTotalIncome, 0);
            $totals['bhxh_amount']      += round($sumBhxh, 0);
            $totals['taxable_income']   += round($sumTaxableIncome, 0);
            $totals['family_deduction'] += round($sumFamilyDeduction, 0);
            $totals['assessable_income'] += $assessableIncome;
            $totals['pit_payable']      += $pitPayable;
            $totals['net_after_tax']    += round($sumNetSalary, 0);
            $totals['pit_withheld']     += round($sumPitWithheld, 0);
            $totals['pit_refund']       += $refund;
        }

        return [
            'period' => $period,
            'year'   => $year,
            'label'  => $this->periodLabel($period, $year),
            'start'  => $range['start'],
            'end'    => $range['end'],
            'rows'   => $rows,
            'totals' => $totals,
        ];
    }
}
