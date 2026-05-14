<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Overtime;
use App\Models\ProductSalary;
use App\Models\Allowance;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $employees = [
            [
                'employee_code' => 'NV001', 'full_name' => 'Nguyễn Văn An',
                'position' => 'Công nhân SX', 'department' => 'Phân xưởng A',
                'basic_salary' => 8_000_000, 'bhxh_salary' => 6_000_000,
                'diligence_bonus' => 300_000, 'dependents' => 1,
            ],
            [
                'employee_code' => 'NV002', 'full_name' => 'Trần Thị Bích',
                'position' => 'Tổ trưởng', 'department' => 'Phân xưởng A',
                'basic_salary' => 15_000_000, 'bhxh_salary' => 12_000_000,
                'diligence_bonus' => 500_000, 'dependents' => 2,
            ],
            [
                'employee_code' => 'NV003', 'full_name' => 'Lê Quốc Cường',
                'position' => 'Quản đốc', 'department' => 'Phân xưởng B',
                'basic_salary' => 25_000_000, 'bhxh_salary' => 20_000_000,
                'diligence_bonus' => 1_000_000, 'dependents' => 0,
            ],
            [
                'employee_code' => 'NV004', 'full_name' => 'Phạm Thu Dung',
                'position' => 'Kế toán', 'department' => 'Văn phòng',
                'basic_salary' => 12_000_000, 'bhxh_salary' => 10_000_000,
                'diligence_bonus' => 500_000, 'dependents' => 1,
            ],
            [
                'employee_code' => 'NV005', 'full_name' => 'Hoàng Minh Đức',
                'position' => 'Giám đốc', 'department' => 'Ban GĐ',
                'basic_salary' => 50_000_000, 'bhxh_salary' => 40_000_000,
                'diligence_bonus' => 2_000_000, 'dependents' => 2,
            ],
        ];

        $year = (int) now()->year;
        $month = (int) now()->month;
        $start = Carbon::create($year, $month, 1);

        foreach ($employees as $data) {
            $emp = Employee::create($data + ['joined_date' => now()->subYears(2), 'is_active' => true]);

            // Chấm công 20 ngày đầu của tháng
            for ($d = 1; $d <= min(20, $start->daysInMonth); $d++) {
                $date = $start->copy()->day($d);
                Attendance::create([
                    'employee_id' => $emp->id,
                    'work_date' => $date,
                    'type' => $date->isSunday() ? 'sunday' : 'normal',
                ]);
                // Random tăng ca cho 1 nửa số ngày
                if ($d % 3 === 0) {
                    Overtime::create([
                        'employee_id' => $emp->id,
                        'work_date' => $date,
                        'shifts' => 1,
                    ]);
                }
            }

            // Lương sản phẩm
            ProductSalary::create([
                'employee_id' => $emp->id, 'year' => $year, 'month' => $month,
                'amount' => $emp->basic_salary * 0.2, 'note' => 'Lương SP tháng',
            ]);

            // Phụ cấp mẫu
            Allowance::create([
                'employee_id' => $emp->id, 'year' => $year, 'month' => $month,
                'name' => 'Phụ cấp xăng xe', 'type' => 'non_taxable', 'amount' => 500_000,
            ]);
            Allowance::create([
                'employee_id' => $emp->id, 'year' => $year, 'month' => $month,
                'name' => 'Phụ cấp trách nhiệm', 'type' => 'taxable', 'amount' => 1_000_000,
            ]);
        }
    }
}