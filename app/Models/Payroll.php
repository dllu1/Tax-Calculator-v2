<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payroll extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id', 'year', 'month',
        'normal_days', 'sunday_days', 'sunday_half_days', 'absent_days', 'half_days', 'overtime_shifts',
        'day_wage', 'overtime_wage', 'meal_shift', 'meal_overtime',
        'product_salary', 'diligence', 'half_day_amount',
        'tet_bonus', 'annual_leave_pay',
        'taxable_allowances', 'non_taxable_allowances',
        'total_income',
        'taxable_income', 'personal_deduction', 'dependent_deduction',
        'bhxh_amount', 'assessable_income', 'pit_amount',
        'advance', 'net_salary', 'detail',
    ];

    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'detail' => 'array',
        'day_wage' => 'decimal:2',
        'overtime_wage' => 'decimal:2',
        'meal_shift' => 'decimal:2',
        'meal_overtime' => 'decimal:2',
        'product_salary' => 'decimal:2',
        'diligence' => 'decimal:2',
        'tet_bonus' => 'decimal:2',
        'annual_leave_pay' => 'decimal:2',
        'taxable_allowances' => 'decimal:2',
        'non_taxable_allowances' => 'decimal:2',
        'total_income' => 'decimal:2',
        'taxable_income' => 'decimal:2',
        'personal_deduction' => 'decimal:2',
        'dependent_deduction' => 'decimal:2',
        'bhxh_amount' => 'decimal:2',
        'assessable_income' => 'decimal:2',
        'pit_amount' => 'decimal:2',
        'advance' => 'decimal:2',
        'net_salary' => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}