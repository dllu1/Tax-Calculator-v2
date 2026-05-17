<?php

namespace App\Services;

use App\Models\Allowance;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Payroll;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PayrollService
{
    public const STANDARD_DAYS = 26;
    public const MEAL_PER_DAY = 30_000;
    public const MEAL_PER_OT_SHIFT = 30_000;

    public function __construct(
        private readonly TaxService $tax,
        private readonly SettingService $settings,
    ) {
    }

    /**
     * Tính bảng lương cho 1 nhân viên trong tháng/năm
     * và lưu vào bảng payrolls (upsert theo employee_id+year+month).
     */
    public function calculate(Employee $employee, int $year, int $month): Payroll
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        // Tham số có thể cấu hình
        $standardDays = max(1, (int) $this->settings->number('payroll.standard_days', self::STANDARD_DAYS));
        $mealPerDay = $this->settings->number('payroll.meal_per_day', self::MEAL_PER_DAY);
        $mealPerOt = $this->settings->number('payroll.meal_per_ot_shift', self::MEAL_PER_OT_SHIFT);
        $sundayMultiplier = $this->settings->number('payroll.sunday_multiplier', 2);
        $overtimeMultiplier = $this->settings->number('payroll.overtime_multiplier', 0.5);

        // Đếm công
        $attendances = $employee->attendances()
            ->whereBetween('work_date', [$start, $end])
            ->get();

        $normalDays = $attendances->where('type', Attendance::TYPE_NORMAL)->count();
        $sundayDays = $attendances->where('type', Attendance::TYPE_SUNDAY)->count();
        $sundayHalfDays = $attendances->where('type', Attendance::TYPE_SUNDAY_HALF)->count();
        $absentDays = $attendances->where('type', Attendance::TYPE_ABSENT)->count();
        $halfDays = $attendances->where('type', Attendance::TYPE_HALF)->count();

        // Tăng ca
        $overtimeShifts = (int) $employee->overtimes()
            ->whereBetween('work_date', [$start, $end])
            ->sum('shifts');

        // Lương sản phẩm
        $productSalary = (float) ($employee->productSalaries()
            ->where(['year' => $year, 'month' => $month])
            ->value('amount') ?? 0);

        // Phụ cấp
        $allowances = $employee->allowances()
            ->where(['year' => $year, 'month' => $month])
            ->get();
        $taxableAllowances = (float) $allowances->where('type', Allowance::TYPE_TAXABLE)->sum('amount');
        $nonTaxableAllowances = (float) $allowances->where('type', Allowance::TYPE_NON_TAXABLE)->sum('amount');

        // Tạm ứng
        $advance = (float) ($employee->advances()
            ->where(['year' => $year, 'month' => $month])
            ->sum('amount'));

        // Lương ngày
        $basicSalary = (float) $employee->basic_salary;
        $dailyRate = $standardDays > 0 ? $basicSalary / $standardDays : 0;
        // Chủ nhật cả ngày: hệ số cấu hình (×2). Nửa ngày chủ nhật: bằng đúng 1 ngày công
        // (nửa ngày × hệ số CN = 0.5 × 2 = 1) — theo yêu cầu của user.
        $totalWorkDays = $normalDays + ($sundayDays * $sundayMultiplier) + $sundayHalfDays;
        $dayWage = round($dailyRate * $totalWorkDays, 0);

        // Tăng ca theo hệ số cấu hình
        $overtimeWage = round($dailyRate * $overtimeMultiplier * $overtimeShifts, 0);

        // Tiền ăn (theo ngày có mặt thực tế). Nửa ngày CN cũng được tính 1 suất cơm như half
        // (đồng nhất với cách xử lý half-day thường — ăn đủ 1 bữa dù chỉ làm nửa ngày).
        $mealShift = $mealPerDay * ($normalDays + $sundayDays + $halfDays + $sundayHalfDays);
        $mealOvertime = $mealPerOt * $overtimeShifts;

        // Chuyên cần: chỉ trả nếu không nghỉ ngày nào (absent=0). "leave", "half", "sunday_half" không phá chuyên cần.
        $diligence = $absentDays === 0 && ($normalDays + $sundayDays + $halfDays + $sundayHalfDays) > 0
            ? (float) $employee->diligence_bonus
            : 0.0;

        // Lương nửa ngày: mỗi ngày half-day = chuyên cần / 2 (theo cấu hình của người dùng)
        $halfDayAmount = round($halfDays * ((float) $employee->diligence_bonus / 2), 0);

        // TỔNG THỰC NHẬN
        $totalIncome = $dayWage + $overtimeWage + $mealShift + $mealOvertime
            + $productSalary + $diligence + $halfDayAmount
            + $taxableAllowances + $nonTaxableAllowances;

        // === THUẾ TNCN ===
        // TN tính thuế = Lương căn bản + Lương SP + Phụ cấp chịu thuế
        $taxableIncome = $basicSalary + $productSalary + $taxableAllowances;

        // Giảm trừ
        $personalDeduction = $this->tax->personalDeductionAmount();
        $dependentDeduction = $employee->dependents * $this->tax->dependentDeductionAmount();
        $bhxhAmount = $this->tax->bhxhAmount((float) $employee->bhxh_salary);

        // TN chịu thuế
        $assessableIncome = max(0, $taxableIncome - $personalDeduction - $dependentDeduction - $bhxhAmount);

        $pit = $this->tax->calculatePIT($assessableIncome);
        $pitAmount = $pit['tax'];

        // Tiền lương còn lại
        $netSalary = $totalIncome - $advance - $bhxhAmount - $pitAmount;

        $detail = [
            'daily_rate' => $dailyRate,
            'total_work_days' => $totalWorkDays,
            'pit_rate' => $pit['rate'],
            'pit_deduction' => $pit['deduction'],
            'config' => [
                'standard_days' => $standardDays,
                'meal_per_day' => $mealPerDay,
                'meal_per_ot_shift' => $mealPerOt,
                'sunday_multiplier' => $sundayMultiplier,
                'overtime_multiplier' => $overtimeMultiplier,
            ],
        ];

        return DB::transaction(function () use (
            $employee, $year, $month,
            $normalDays, $sundayDays, $sundayHalfDays, $absentDays, $halfDays, $overtimeShifts,
            $dayWage, $overtimeWage, $mealShift, $mealOvertime,
            $productSalary, $diligence, $halfDayAmount, $taxableAllowances, $nonTaxableAllowances,
            $totalIncome, $taxableIncome, $personalDeduction, $dependentDeduction,
            $bhxhAmount, $assessableIncome, $pitAmount, $advance, $netSalary, $detail
        ) {
            return Payroll::updateOrCreate(
                ['employee_id' => $employee->id, 'year' => $year, 'month' => $month],
                [
                    'normal_days' => $normalDays,
                    'sunday_days' => $sundayDays,
                    'sunday_half_days' => $sundayHalfDays,
                    'absent_days' => $absentDays,
                    'half_days' => $halfDays,
                    'overtime_shifts' => $overtimeShifts,
                    'day_wage' => $dayWage,
                    'overtime_wage' => $overtimeWage,
                    'meal_shift' => $mealShift,
                    'meal_overtime' => $mealOvertime,
                    'product_salary' => $productSalary,
                    'diligence' => $diligence,
                    'half_day_amount' => $halfDayAmount,
                    'taxable_allowances' => $taxableAllowances,
                    'non_taxable_allowances' => $nonTaxableAllowances,
                    'total_income' => $totalIncome,
                    'taxable_income' => $taxableIncome,
                    'personal_deduction' => $personalDeduction,
                    'dependent_deduction' => $dependentDeduction,
                    'bhxh_amount' => $bhxhAmount,
                    'assessable_income' => $assessableIncome,
                    'pit_amount' => $pitAmount,
                    'advance' => $advance,
                    'net_salary' => $netSalary,
                    'detail' => $detail,
                ]
            );
        });
    }
}
