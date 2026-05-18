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
}