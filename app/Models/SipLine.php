<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SipLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_name',
        'employee_id',
        'phone_number',
        'virtual_number',
    ];

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    protected function description(): Attribute
    {
        return Attribute::make(
            get: fn(mixed $value, array $attributes) => "{$attributes['employee_name']} ({$attributes['phone_number']})",
        );
    }
}
