<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryChange extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'effective_year',
        'effective_month',
        'basic_salary',
        'bhxh_salary',
        'diligence_bonus',
        'note',
    ];

    protected $casts = [
        'effective_year'   => 'integer',
        'effective_month'  => 'integer',
        'basic_salary'     => 'decimal:2',
        'bhxh_salary'      => 'decimal:2',
        'diligence_bonus'  => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Khoá so sánh thứ tự thời gian (year*100 + month). Dùng cho ORDER BY.
     */
    public function getPeriodKeyAttribute(): int
    {
        return (int) $this->effective_year * 100 + (int) $this->effective_month;
    }
}
