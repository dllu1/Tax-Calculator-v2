<?php

namespace Database\Seeders;

use App\Models\Advance;
use App\Models\Allowance;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Overtime;
use App\Models\ProductSalary;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $employees = [
            ['employee_code' => 'NV001', 'full_name' => 'Nguyễn Văn An',    'position' => 'Công nhân SX',  'department' => 'Phân xưởng A', 'basic_salary' => 8_000_000,  'bhxh_salary' => 6_000_000,  'diligence_bonus' => 300_000,   'dependents' => 1, 'tax_code' => '0790780082980', 'profile' => 'worker'],
            ['employee_code' => 'NV002', 'full_name' => 'Trần Thị Bích',    'position' => 'Tổ trưởng',      'department' => 'Phân xưởng A', 'basic_salary' => 15_000_000, 'bhxh_salary' => 12_000_000, 'diligence_bonus' => 500_000,   'dependents' => 2, 'tax_code' => '0791750097120', 'profile' => 'lead'],
            ['employee_code' => 'NV003', 'full_name' => 'Lê Quốc Cường',    'position' => 'Quản đốc',       'department' => 'Phân xưởng B', 'basic_salary' => 25_000_000, 'bhxh_salary' => 20_000_000, 'diligence_bonus' => 1_000_000, 'dependents' => 0, 'tax_code' => '0790820349280', 'profile' => 'manager'],
            ['employee_code' => 'NV004', 'full_name' => 'Phạm Thu Dung',    'position' => 'Kế toán',        'department' => 'Văn phòng',    'basic_salary' => 12_000_000, 'bhxh_salary' => 10_000_000, 'diligence_bonus' => 500_000,   'dependents' => 1, 'tax_code' => '0790780098610', 'profile' => 'office'],
            ['employee_code' => 'NV005', 'full_name' => 'Hoàng Minh Đức',   'position' => 'Giám đốc',       'department' => 'Ban GĐ',        'basic_salary' => 50_000_000, 'bhxh_salary' => 40_000_000, 'diligence_bonus' => 2_000_000, 'dependents' => 2, 'tax_code' => '0790800230700', 'profile' => 'director'],
            ['employee_code' => 'NV006', 'full_name' => 'Vũ Thị Hà',        'position' => 'Công nhân SX',   'department' => 'Phân xưởng A', 'basic_salary' => 9_500_000,  'bhxh_salary' => 7_500_000,  'diligence_bonus' => 400_000,   'dependents' => 0, 'tax_code' => '0791750352470', 'profile' => 'worker'],
            ['employee_code' => 'NV007', 'full_name' => 'Đỗ Văn Khánh',     'position' => 'Công nhân SX',   'department' => 'Phân xưởng B', 'basic_salary' => 10_000_000, 'bhxh_salary' => 8_000_000,  'diligence_bonus' => 400_000,   'dependents' => 2, 'tax_code' => '0520910112770', 'profile' => 'worker_absent'],
            ['employee_code' => 'NV008', 'full_name' => 'Bùi Thị Lan',      'position' => 'Nhân viên QA',   'department' => 'Phân xưởng B', 'basic_salary' => 14_000_000, 'bhxh_salary' => 11_000_000, 'diligence_bonus' => 500_000,   'dependents' => 1, 'tax_code' => '3011710097640', 'profile' => 'office'],
            ['employee_code' => 'NV009', 'full_name' => 'Ngô Thanh Mai',    'position' => 'Nhân sự',        'department' => 'Văn phòng',    'basic_salary' => 13_500_000, 'bhxh_salary' => 11_000_000, 'diligence_bonus' => 500_000,   'dependents' => 3, 'tax_code' => '0721800017460', 'profile' => 'office'],
            ['employee_code' => 'NV010', 'full_name' => 'Trịnh Quang Phú',  'position' => 'Phó GĐ kinh doanh','department' => 'Ban GĐ',      'basic_salary' => 35_000_000, 'bhxh_salary' => 28_000_000, 'diligence_bonus' => 1_500_000, 'dependents' => 1, 'tax_code' => '0842000058270', 'profile' => 'manager'],
        ];

        $today = now();

        // Phủ trọn 1 chu kỳ quyết toán: Tháng 12 năm trước → Tháng 11 năm nay.
        // Như vậy cả 4 quý và bản quyết toán Cả năm đều có đủ dữ liệu để test.
        // Mặc định "năm quyết toán" = năm hiện tại; nếu đang trong tháng 12 thì
        // bắt đầu chu kỳ mới (Dec(curr) → Nov(next)).
        $settlementYear = $today->month === 12 ? $today->year + 1 : $today->year;

        $months = [];
        // Tháng 12 năm trước
        $months[] = Carbon::create($settlementYear - 1, 12, 1)->startOfMonth();
        // Tháng 1 → 11 năm settlement
        for ($m = 1; $m <= 11; $m++) {
            $months[] = Carbon::create($settlementYear, $m, 1)->startOfMonth();
        }

        foreach ($employees as $idx => $data) {
            $profile = $data['profile'];
            unset($data['profile']);

            $emp = Employee::create($data + [
                'joined_date' => $today->copy()->subYears(2)->subMonths($idx),
                'is_active'   => true,
            ]);

            foreach ($months as $monthDate) {
                $this->seedMonthForEmployee($emp, $monthDate, $profile, $idx);
            }
        }
    }

    private function seedMonthForEmployee(Employee $emp, Carbon $monthDate, string $profile, int $idx): void
    {
        $year     = (int) $monthDate->year;
        $month    = (int) $monthDate->month;
        $daysInMo = $monthDate->daysInMonth;

        // Logic chấm công:
        // - Tháng đã qua / tháng tương lai (cho quyết toán): chấm trọn tháng.
        // - Tháng hiện tại: chỉ chấm tới ngày hôm nay.
        $isCurrentMonth = $monthDate->isSameMonth(now());
        $lastWorkDay    = $isCurrentMonth ? min(now()->day, $daysInMo) : $daysInMo;

        // Ngày đặc biệt theo profile + tháng để có biến động giữa các kỳ:
        // - nửa ngày (half): 1-2 ngày
        // - nghỉ phép (leave): 1 ngày
        // - nghỉ không phép (absent): chỉ NV007 (worker_absent)
        $halfDays   = $this->daysFor($profile, $monthDate, ['half_a', 'half_b']);
        $leaveDays  = $this->daysFor($profile, $monthDate, ['leave']);
        $absentDays = $profile === 'worker_absent' ? $this->daysFor($profile, $monthDate, ['absent']) : [];

        for ($d = 1; $d <= $lastWorkDay; $d++) {
            $date = $monthDate->copy()->day($d);

            if (in_array($d, $absentDays, true)) {
                $type = Attendance::TYPE_ABSENT;
            } elseif (in_array($d, $leaveDays, true)) {
                $type = Attendance::TYPE_LEAVE;
            } elseif (in_array($d, $halfDays, true)) {
                $type = Attendance::TYPE_HALF;
            } elseif ($date->isSunday()) {
                $type = Attendance::TYPE_SUNDAY;
            } else {
                $type = Attendance::TYPE_NORMAL;
            }

            Attendance::create([
                'employee_id' => $emp->id,
                'work_date'   => $date,
                'type'        => $type,
            ]);

            // Tăng ca: chỉ công nhân & tổ trưởng — 2 ca mỗi 5 ngày, 1 ca mỗi 3 ngày
            if (in_array($profile, ['worker', 'worker_absent', 'lead'], true) && $type !== Attendance::TYPE_ABSENT && $type !== Attendance::TYPE_LEAVE) {
                if ($d % 5 === 0) {
                    Overtime::create([
                        'employee_id' => $emp->id, 'work_date' => $date, 'shifts' => 2,
                        'note' => 'Tăng ca cuối tuần',
                    ]);
                } elseif ($d % 3 === 0) {
                    Overtime::create([
                        'employee_id' => $emp->id, 'work_date' => $date, 'shifts' => 1,
                    ]);
                }
            }
        }

        // Lương sản phẩm: chỉ cho worker/lead, tỷ lệ ~15-25% lương căn bản
        if (in_array($profile, ['worker', 'worker_absent', 'lead'], true)) {
            $rate = $profile === 'lead' ? 0.25 : 0.20;
            ProductSalary::create([
                'employee_id' => $emp->id, 'year' => $year, 'month' => $month,
                'amount' => (int) ($emp->basic_salary * $rate),
                'note'   => 'Lương SP tháng ' . $month,
            ]);
        }

        // Phụ cấp — đa dạng theo profile
        $allowances = match ($profile) {
            'director', 'manager' => [
                ['Phụ cấp trách nhiệm',  'taxable',     2_000_000],
                ['Phụ cấp điện thoại',   'non_taxable',   500_000],
                ['Phụ cấp xăng xe',      'non_taxable',   800_000],
            ],
            'lead' => [
                ['Phụ cấp trách nhiệm',  'taxable',     1_000_000],
                ['Phụ cấp xăng xe',      'non_taxable',   500_000],
            ],
            'office' => [
                ['Phụ cấp ăn trưa',      'non_taxable',   730_000],
                ['Phụ cấp điện thoại',   'non_taxable',   300_000],
            ],
            default => [ // worker / worker_absent
                ['Phụ cấp xăng xe',      'non_taxable',   500_000],
                ['Phụ cấp độc hại',      'taxable',       600_000],
            ],
        };
        foreach ($allowances as [$name, $type, $amount]) {
            Allowance::create([
                'employee_id' => $emp->id, 'year' => $year, 'month' => $month,
                'name' => $name, 'type' => $type, 'amount' => $amount,
            ]);
        }

        // Tạm ứng — chỉ một số người (manager, office, NV001, NV007) để test
        $shouldAdvance = match (true) {
            $profile === 'manager'                          => true,
            $profile === 'office' && $idx % 2 === 0         => true,
            in_array($emp->employee_code, ['NV001', 'NV007'], true) => true,
            default                                          => false,
        };
        if ($shouldAdvance) {
            Advance::create([
                'employee_id'  => $emp->id, 'year' => $year, 'month' => $month,
                'amount'       => $profile === 'manager' ? 5_000_000 : 2_000_000,
                'advance_date' => $monthDate->copy()->day(min(15, $daysInMo)),
                'note'         => 'Ứng lương giữa tháng',
            ]);
        }
    }

    /**
     * Sinh các ngày trong tháng (4-27 để an toàn) cho profile + tag.
     * Tạo deterministic theo employee_code + tháng để dữ liệu re-runnable.
     */
    private function daysFor(string $profile, Carbon $monthDate, array $tags): array
    {
        $base = crc32($profile . $monthDate->format('Y-m'));
        $picks = [];
        foreach ($tags as $tag) {
            $offset = ($base + crc32($tag)) % 24; // 0..23
            $picks[] = (int) $offset + 4;          // 4..27 (tránh ngày 1-3 đầu tháng)
        }
        return array_values(array_unique($picks));
    }
}
