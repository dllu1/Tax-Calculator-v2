<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_code',
        'full_name',
        'position',
        'department',
        'joined_date',
        'dob',
        'tax_code',
        'id_card',
        'phone',
        'address',
        'basic_salary',
        'bhxh_salary',
        'diligence_bonus',
        'tet_bonus',
        'annual_leave_pay',
        'dependents',
        'is_active',
    ];

    protected $casts = [
        'joined_date' => 'date',
        'dob' => 'date',
        'basic_salary' => 'decimal:2',
        'bhxh_salary' => 'decimal:2',
        'diligence_bonus' => 'decimal:2',
        'tet_bonus' => 'decimal:2',
        'annual_leave_pay' => 'decimal:2',
        'dependents' => 'integer',
        'is_active' => 'boolean',
    ];

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function overtimes(): HasMany
    {
        return $this->hasMany(Overtime::class);
    }

    public function productSalaries(): HasMany
    {
        return $this->hasMany(ProductSalary::class);
    }

    public function allowances(): HasMany
    {
        return $this->hasMany(Allowance::class);
    }

    public function advances(): HasMany
    {
        return $this->hasMany(Advance::class);
    }

    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class);
    }

    public function dependentRecords(): HasMany
    {
        return $this->hasMany(Dependent::class);
    }

    public function salaryChanges(): HasMany
    {
        return $this->hasMany(SalaryChange::class);
    }

    /**
     * Tìm mức lương "có hiệu lực" tại tháng cụ thể: chọn SalaryChange mới
     * nhất có (effective_year, effective_month) ≤ (year, month). Các tháng
     * trước đợt thay đổi đầu tiên dùng giá trị gốc trên Employee.
     *
     * Các trường nullable của SalaryChange (bhxh_salary, diligence_bonus)
     * tự fall back về đợt thay đổi áp dụng trước nó, cuối cùng về Employee.
     *
     * @return array{basic_salary:float, bhxh_salary:float, diligence_bonus:float}
     */
    public function effectiveSalary(int $year, int $month): array
    {
        $key = $year * 100 + $month;

        // Lấy mọi đợt áp dụng tính tới (year, month), sắp xếp mới nhất trước.
        $applicable = $this->salaryChanges()
            ->whereRaw('(effective_year * 100 + effective_month) <= ?', [$key])
            ->orderByDesc('effective_year')
            ->orderByDesc('effective_month')
            ->get();

        // Bắt đầu từ giá trị gốc trên Employee — fallback cuối cùng.
        $result = [
            'basic_salary'    => (float) $this->basic_salary,
            'bhxh_salary'     => (float) $this->bhxh_salary,
            'diligence_bonus' => (float) $this->diligence_bonus,
        ];

        // Áp đợt cũ trước, đợt mới sau → đợt mới sẽ ghi đè trường non-null.
        foreach ($applicable->reverse() as $change) {
            $result['basic_salary'] = (float) $change->basic_salary;
            if ($change->bhxh_salary !== null) {
                $result['bhxh_salary'] = (float) $change->bhxh_salary;
            }
            if ($change->diligence_bonus !== null) {
                $result['diligence_bonus'] = (float) $change->diligence_bonus;
            }
        }

        return $result;
    }
}