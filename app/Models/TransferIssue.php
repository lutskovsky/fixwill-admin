<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 *
 *
 * @property int $id
 * @property int $order_id
 * @property string $date
 * @property string $phones
 * @property int $called
 * @property int $processed
 * @property int $postponed
 * @property int $escalated
 * @property string $reason
 * @property string $result
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransferIssue newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransferIssue newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransferIssue query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransferIssue whereCalled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransferIssue whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransferIssue whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransferIssue whereEscalated($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransferIssue whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransferIssue whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransferIssue wherePhones($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransferIssue wherePostponed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransferIssue whereProcessed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransferIssue whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransferIssue whereResult($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransferIssue whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TransferIssue extends Model
{
    use SoftDeletes;

    protected $guarded = [];
    protected $casts = [
        'phones' => 'array',
    ];
}
