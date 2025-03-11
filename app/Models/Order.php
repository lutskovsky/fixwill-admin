<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 *
 *
 * @property int $id
 * @property string $label
 * @property array $data
 * @property mixed $initial_pickup_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder<static>|Order newModelQuery()
 * @method static Builder<static>|Order newQuery()
 * @method static Builder<static>|Order query()
 * @method static Builder<static>|Order whereCreatedAt($value)
 * @method static Builder<static>|Order whereData($value)
 * @method static Builder<static>|Order whereId($value)
 * @method static Builder<static>|Order whereLabel($value)
 * @method static Builder<static>|Order whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Order extends Model
{
    public $incrementing = false;
    protected $guarded = [];
    protected $casts = [
        'data' => 'array'
    ];
}
