<?php

namespace App\Services;

use App\Models\Allowance;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Overtime;
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
        // Tổng công quy đổi:
        //   - Ngày thường: 1 công/ngày
        //   - Nửa ngày thường: 0.5 công
        //   - Chủ nhật cả ngày: × hệ số cấu hình (mặc định ×2)
        //   - Nửa ngày chủ nhật: 0.5 × hệ số = 1 công (bằng đúng 1 ngày công, theo yêu cầu)
        $totalWorkDays = $normalDays
            + ($halfDays * 0.5)
            + ($sundayDays * $sundayMultiplier)
            + ($sundayHalfDays * 0.5 * $sundayMultiplier);
        $dayWage = round($dailyRate * $totalWorkDays, 0);

        // Tăng ca theo hệ số cấu hình
        $overtimeWage = round($dailyRate * $overtimeMultiplier * $overtimeShifts, 0);

        // Tiền ăn giữa ca: chỉ áp dụng cho ngày đi làm CẢ NGÀY (normal + sunday).
        // Nửa ngày (half + sunday_half) không có tiền ăn giữa ca — theo yêu cầu của user.
        $mealShift = $mealPerDay * ($normalDays + $sundayDays);
        $mealOvertime = $mealPerOt * $overtimeShifts;

        // Chuyên cần: chỉ trả nếu không nghỉ ngày nào (absent=0). "leave", "half", "sunday_half" không phá chuyên cần.
        $diligence = $absentDays === 0 && ($normalDays + $sundayDays + $halfDays + $sundayHalfDays) > 0
            ? (float) $employee->diligence_bonus
            : 0.0;

        // Lương nửa ngày đã được cộng vào $dayWage qua $totalWorkDays (halfDays × 0.5).
        // Cột half_day_amount giữ lại trên payroll record với giá trị 0 cho tương thích DB.
        $halfDayAmount = 0;

        // Thưởng Tết & lương phép năm (cố định trên hồ sơ NV — user tự reset về 0 khi tháng
        // đã trả xong nếu chỉ phát 1 lần/năm).
        $tetBonus = (float) ($employee->tet_bonus ?? 0);
        $annualLeavePay = (float) ($employee->annual_leave_pay ?? 0);

        // TỔNG THỰC NHẬN
        $totalIncome = $dayWage + $overtimeWage + $mealShift + $mealOvertime
            + $productSalary + $diligence + $halfDayAmount
            + $tetBonus + $annualLeavePay
            + $taxableAllowances + $nonTaxableAllowances;

        // === THUẾ TNCN ===
        // TN tính thuế = Lương ngày công (theo số ngày đi làm, KHÔNG phải lương căn bản)
        //              + Chuyên cần + Lương SP + Phụ cấp chịu thuế + Thưởng Tết + Lương phép năm
        $taxableIncome = $dayWage + $diligence + $productSalary + $taxableAllowances
            + $tetBonus + $annualLeavePay;

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
            $productSalary, $diligence, $halfDayAmount,
            $tetBonus, $annualLeavePay,
            $taxableAllowances, $nonTaxableAllowances,
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
                    'tet_bonus' => $tetBonus,
                    'annual_leave_pay' => $annualLeavePay,
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

    /**
     * Build the rich monthly summary data set for the PDF report.
     * Pivots Overtime into weekday / Sunday buckets and gathers the unique
     * non-taxable allowance names so the PDF view can render dynamic columns.
     *
     * @return array{
     *   year:int, month:int,
     *   allowance_names: string[],
     *   rows: array<int, array<string, mixed>>,
     *   totals: array<string, float>,
     * }
     */
    public function buildSummaryReport(int $year, int $month): array
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $standardDays = max(1, (int) $this->settings->number('payroll.standard_days', self::STANDARD_DAYS));
        $mealPerOt = (float) $this->settings->number('payroll.meal_per_ot_shift', self::MEAL_PER_OT_SHIFT);
        $overtimeMultiplier = (float) $this->settings->number('payroll.overtime_multiplier', 0.5);
        $employerBhxhRate = 0.215;

        $employees = Employee::where('is_active', true)->orderBy('employee_code')->get();

        // Unique non-taxable allowance names this month → dynamic PC-không-tính-thuế columns.
        $allowanceNames = Allowance::whereIn('employee_id', $employees->pluck('id'))
            ->where('year', $year)->where('month', $month)
            ->where('type', Allowance::TYPE_NON_TAXABLE)
            ->orderBy('name')
            ->pluck('name')
            ->unique()
            ->values()
            ->all();

        // Pre-load all OT records for the month, grouped by employee_id then by weekday/Sunday.
        $allOvertimes = Overtime::whereIn('employee_id', $employees->pluck('id'))
            ->whereBetween('work_date', [$start, $end])
            ->get()
            ->groupBy('employee_id');

        $rows = [];
        $totals = array_fill_keys([
            'day_wage', 'meal_shift',
            'ot_weekday_shifts', 'ot_weekday_wage', 'ot_weekday_meal',
            'ot_sunday_shifts', 'ot_sunday_wage', 'ot_sunday_meal',
            'product_salary', 'tet_bonus', 'annual_leave_pay',
            'total_income', 'taxable_income',
            'bhxh_salary', 'employer_bhxh', 'bhxh_amount',
            'advance', 'personal_deduction', 'dependent_deduction',
            'assessable_income', 'pit_amount', 'net_salary',
        ], 0.0);
        $totalsByAllowance = array_fill_keys($allowanceNames, 0.0);

        foreach ($employees as $emp) {
            // Run the calculation (also persists the Payroll row — keeps DB & PDF in sync).
            $payroll = $this->calculate($emp, $year, $month);

            $dailyRate = (float) ($payroll->detail['daily_rate'] ?? ($emp->basic_salary / $standardDays));

            // Split OT by weekday vs Sunday based on the work_date.
            $empOts = $allOvertimes->get($emp->id, collect());
            $weekdayOtShifts = (int) $empOts->filter(fn ($o) => !$o->work_date->isSunday())->sum('shifts');
            $sundayOtShifts = (int) $empOts->filter(fn ($o) => $o->work_date->isSunday())->sum('shifts');

            $weekdayOtWage = round($dailyRate * $overtimeMultiplier * $weekdayOtShifts, 0);
            $sundayOtWage = round($dailyRate * $overtimeMultiplier * $sundayOtShifts, 0);
            $weekdayOtMeal = $mealPerOt * $weekdayOtShifts;
            $sundayOtMeal = $mealPerOt * $sundayOtShifts;

            // Total work days (đã quy đổi half = 0.5, sunday × multiplier, sunday_half × 0.5 × multiplier
            // — đã tính sẵn trong PayrollService và lưu vào detail.total_work_days).
            $workDaysTotal = (float) ($payroll->detail['total_work_days'] ?? 0);
            $mealDays = (int) ($payroll->normal_days + $payroll->sunday_days);

            // Per-allowance breakdown (non-taxable only).
            $allowancesByName = [];
            foreach ($allowanceNames as $name) {
                $sum = (float) Allowance::where('employee_id', $emp->id)
                    ->where('year', $year)->where('month', $month)
                    ->where('type', Allowance::TYPE_NON_TAXABLE)
                    ->where('name', $name)
                    ->sum('amount');
                $allowancesByName[$name] = $sum;
                $totalsByAllowance[$name] += $sum;
            }

            $employerBhxh = round($employerBhxhRate * (float) $emp->bhxh_salary, 0);

            $row = [
                'employee' => $emp,
                'payroll' => $payroll,
                'work_days_total' => $workDaysTotal,
                'meal_days' => $mealDays,
                'ot_weekday_shifts' => $weekdayOtShifts,
                'ot_weekday_wage' => $weekdayOtWage,
                'ot_weekday_meal' => $weekdayOtMeal,
                'ot_sunday_shifts' => $sundayOtShifts,
                'ot_sunday_wage' => $sundayOtWage,
                'ot_sunday_meal' => $sundayOtMeal,
                'allowances_by_name' => $allowancesByName,
                'employer_bhxh' => $employerBhxh,
            ];
            $rows[] = $row;

            // Accumulate column totals
            $totals['day_wage'] += (float) $payroll->day_wage;
            $totals['meal_shift'] += (float) $payroll->meal_shift;
            $totals['ot_weekday_shifts'] += $weekdayOtShifts;
            $totals['ot_weekday_wage'] += $weekdayOtWage;
            $totals['ot_weekday_meal'] += $weekdayOtMeal;
            $totals['ot_sunday_shifts'] += $sundayOtShifts;
            $totals['ot_sunday_wage'] += $sundayOtWage;
            $totals['ot_sunday_meal'] += $sundayOtMeal;
            $totals['product_salary'] += (float) $payroll->product_salary;
            $totals['tet_bonus'] += (float) $payroll->tet_bonus;
            $totals['annual_leave_pay'] += (float) $payroll->annual_leave_pay;
            $totals['total_income'] += (float) $payroll->total_income;
            $totals['taxable_income'] += (float) $payroll->taxable_income;
            $totals['bhxh_salary'] += (float) $emp->bhxh_salary;
            $totals['employer_bhxh'] += $employerBhxh;
            $totals['bhxh_amount'] += (float) $payroll->bhxh_amount;
            $totals['advance'] += (float) $payroll->advance;
            $totals['personal_deduction'] += (float) $payroll->personal_deduction;
            $totals['dependent_deduction'] += (float) $payroll->dependent_deduction;
            $totals['assessable_income'] += (float) $payroll->assessable_income;
            $totals['pit_amount'] += (float) $payroll->pit_amount;
            $totals['net_salary'] += (float) $payroll->net_salary;
        }

        return [
            'year' => $year,
            'month' => $month,
            'allowance_names' => $allowanceNames,
            'rows' => $rows,
            'totals' => $totals,
            'totals_by_allowance' => $totalsByAllowance,
        ];
    }
}
