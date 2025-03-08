<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 *
 *
 * @property int $id
 * @property int $status_id
 * @property string $status_name
 * @property int $current
 * @property int $accepted_by_operator
 * @property int $success_for_operator
 * @property int $transit
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $operator_required
 * @property int $reschedule_before
 * @property int $reschedule_after
 * @property int $refusal_before
 * @property int $refusal_after
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Status newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Status newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Status query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Status whereAcceptedByOperator($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Status whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Status whereCurrent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Status whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Status whereOperatorRequired($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Status whereRefusalAfter($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Status whereRefusalBefore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Status whereRescheduleAfter($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Status whereRescheduleBefore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Status whereStatusId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Status whereStatusName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Status whereSuccessForOperator($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Status whereTransit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Status whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Status extends Model
{
    use CrudTrait;
    use HasFactory;

    protected $fillable = [
        'status_id',
        'status_name',
        'current',
        'accepted_by_operator',
        'success_for_operator',
        'transit',
        'operator_required',
        'reschedule_before',
        'reschedule_after',
        'refusal_before',
        'refusal_after',
    ];
}
