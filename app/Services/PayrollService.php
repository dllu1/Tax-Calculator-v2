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

    public function __construct(private readonly TaxService $tax)
    {
    }

    /**
     * Tính bảng lương cho 1 nhân viên trong tháng/năm
     * và lưu vào bảng payrolls (upsert theo employee_id+year+month).
     */
    public function calculate(Employee $employee, int $year, int $month): Payroll
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();
        $daysInMonth = $start->daysInMonth;

        // Đếm công
        $attendances = $employee->attendances()
            ->whereBetween('work_date', [$start, $end])
            ->get();

        $normalDays = $attendances->where('type', Attendance::TYPE_NORMAL)->count();
        $sundayDays = $attendances->where('type', Attendance::TYPE_SUNDAY)->count();
        $absentDays = $attendances->where('type', Attendance::TYPE_ABSENT)->count();

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
        $dailyRate = self::STANDARD_DAYS > 0 ? $basicSalary / self::STANDARD_DAYS : 0;
        // Chủ nhật = 2 ngày công
        $totalWorkDays = $normalDays + ($sundayDays * 2);
        $dayWage = round($dailyRate * $totalWorkDays, 0);

        // Tăng ca 3h = nửa ngày công
        $overtimeWage = round($dailyRate * 0.5 * $overtimeShifts, 0);

        // Tiền ăn (theo ngày có mặt thực tế = normal + sunday)
        $mealShift = self::MEAL_PER_DAY * ($normalDays + $sundayDays);
        $mealOvertime = self::MEAL_PER_OT_SHIFT * $overtimeShifts;

        // Chuyên cần: chỉ trả nếu không nghỉ ngày nào (absent=0). Bỏ qua "leave" (nghỉ phép có lý do).
        $diligence = $absentDays === 0 && ($normalDays + $sundayDays) > 0
            ? (float) $employee->diligence_bonus
            : 0.0;

        // TỔNG THỰC NHẬN
        $totalIncome = $dayWage + $overtimeWage + $mealShift + $mealOvertime
            + $productSalary + $diligence + $taxableAllowances + $nonTaxableAllowances;

        // === THUẾ TNCN ===
        // TN tính thuế = Lương căn bản + Lương SP + Phụ cấp chịu thuế
        $taxableIncome = $basicSalary + $productSalary + $taxableAllowances;

        // Giảm trừ
        $personalDeduction = TaxService::PERSONAL_DEDUCTION;
        $dependentDeduction = $employee->dependents * TaxService::DEPENDENT_DEDUCTION;
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
        ];

        return DB::transaction(function () use (
            $employee, $year, $month,
            $normalDays, $sundayDays, $absentDays, $overtimeShifts,
            $dayWage, $overtimeWage, $mealShift, $mealOvertime,
            $productSalary, $diligence, $taxableAllowances, $nonTaxableAllowances,
            $totalIncome, $taxableIncome, $personalDeduction, $dependentDeduction,
            $bhxhAmount, $assessableIncome, $pitAmount, $advance, $netSalary, $detail
        ) {
            return Payroll::updateOrCreate(
                ['employee_id' => $employee->id, 'year' => $year, 'month' => $month],
                [
                    'normal_days' => $normalDays,
                    'sunday_days' => $sundayDays,
                    'absent_days' => $absentDays,
                    'overtime_shifts' => $overtimeShifts,
                    'day_wage' => $dayWage,
                    'overtime_wage' => $overtimeWage,
                    'meal_shift' => $mealShift,
                    'meal_overtime' => $mealOvertime,
                    'product_salary' => $productSalary,
                    'diligence' => $diligence,
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