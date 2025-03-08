<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 *
 *
 * @property int $id
 * @property int $new_status_id
 * @property int $old_status_id
 * @property int $order_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusChange newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusChange newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusChange query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusChange whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusChange whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusChange whereNewStatusId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusChange whereOldStatusId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusChange whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusChange whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class StatusChange extends Model
{
    use HasFactory;


    protected $fillable = [
        'new_status_id',
        'old_status_id',
        'order_id'
    ];
}
