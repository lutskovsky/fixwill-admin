<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 *
 *
 * @property int $id
 * @property int|null $courier_id
 * @property string $direction
 * @property Courier|null $courier
 * @property int $order_id
 * @property string|null $arrival_time
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $order_label
 * @property string|null $courier_type
 * @property string|null $result
 * @property int $active
 * @property int $moved_on
 * @method static Builder<static>|CourierTrip newModelQuery()
 * @method static Builder<static>|CourierTrip newQuery()
 * @method static Builder<static>|CourierTrip query()
 * @method static Builder<static>|CourierTrip whereActive($value)
 * @method static Builder<static>|CourierTrip whereArrivalTime($value)
 * @method static Builder<static>|CourierTrip whereCourier($value)
 * @method static Builder<static>|CourierTrip whereCourierId($value)
 * @method static Builder<static>|CourierTrip whereCourierType($value)
 * @method static Builder<static>|CourierTrip whereCreatedAt($value)
 * @method static Builder<static>|CourierTrip whereDirection($value)
 * @method static Builder<static>|CourierTrip whereId($value)
 * @method static Builder<static>|CourierTrip whereMovedOn($value)
 * @method static Builder<static>|CourierTrip whereOrderId($value)
 * @method static Builder<static>|CourierTrip whereOrderLabel($value)
 * @method static Builder<static>|CourierTrip whereResult($value)
 * @method static Builder<static>|CourierTrip whereStatus($value)
 * @method static Builder<static>|CourierTrip whereUpdatedAt($value)
 * @mixin Eloquent
 */
class CourierTrip extends Model
{
    use HasFactory;

    protected $table = 'courier_trips';

    protected $fillable = [
        'courier_id',
        'direction',
        'courier',
        'order_id',
        'arrival_time',
        'status',
        'order_label',
        'courier_type',
        'result',
        'active',
        'moved_on',
    ];

    /**
     * Get the courier that owns the CourierTrip.
     */
    public function courier()
    {
        // references the 'courier_id' column in 'courier_trips'
        return $this->belongsTo(Courier::class, 'courier_id');
    }

    protected function scopeCurrent(Builder $query)
    {
        return $query->where('active', true)
            ->orWhere('moved_on', false);
    }
}
