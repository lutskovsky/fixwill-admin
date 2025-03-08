<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 *
 *
 * @property int $id
 * @property string $employee_name
 * @property int $employee_id
 * @property string $phone_number
 * @property string|null $virtual_number
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $description
 * @property-read Model|\Eloquent $owner
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SipLine newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SipLine newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SipLine query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SipLine whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SipLine whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SipLine whereEmployeeName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SipLine whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SipLine wherePhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SipLine whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SipLine whereVirtualNumber($value)
 * @mixin \Eloquent
 */
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
