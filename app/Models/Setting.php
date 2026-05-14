<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    public const TYPE_STRING = 'string';
    public const TYPE_NUMBER = 'number';
    public const TYPE_JSON = 'json';

    public const GROUP_TAX = 'tax';
    public const GROUP_PAYROLL = 'payroll';
    public const GROUP_GENERAL = 'general';

    protected $fillable = ['key', 'value', 'type', 'group', 'label', 'description'];

    public function getDecodedValueAttribute(): mixed
    {
        return match ($this->type) {
            self::TYPE_NUMBER => is_numeric($this->value) ? $this->value + 0 : 0,
            self::TYPE_JSON => json_decode((string) $this->value, true) ?? [],
            default => $this->value,
        };
    }
}
