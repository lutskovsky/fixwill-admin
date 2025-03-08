<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 *
 *
 * @property int $id
 * @property int $type_id
 * @property string $name
 * @property int $operator_required
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderType query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderType whereOperatorRequired($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderType whereTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderType whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class OrderType extends Model
{
    use CrudTrait;
    use HasFactory;

    protected $fillable = [
        'type_id',
        'name',
        'operator_required',
    ];
}
