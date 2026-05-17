<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    public const TYPE_NORMAL = 'normal';
    public const TYPE_SUNDAY = 'sunday';
    public const TYPE_ABSENT = 'absent';
    public const TYPE_LEAVE = 'leave';
    public const TYPE_HALF = 'half';
    public const TYPE_SUNDAY_HALF = 'sunday_half';

    protected $fillable = ['employee_id', 'work_date', 'type', 'note'];

    protected $casts = [
        'work_date' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}