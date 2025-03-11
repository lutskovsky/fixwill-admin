<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

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
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property mixed $message_id
 * @method static Builder<static>|TransferIssue newModelQuery()
 * @method static Builder<static>|TransferIssue newQuery()
 * @method static Builder<static>|TransferIssue query()
 * @method static Builder<static>|TransferIssue whereCalled($value)
 * @method static Builder<static>|TransferIssue whereCreatedAt($value)
 * @method static Builder<static>|TransferIssue whereDate($value)
 * @method static Builder<static>|TransferIssue whereEscalated($value)
 * @method static Builder<static>|TransferIssue whereId($value)
 * @method static Builder<static>|TransferIssue whereOrderId($value)
 * @method static Builder<static>|TransferIssue wherePhones($value)
 * @method static Builder<static>|TransferIssue wherePostponed($value)
 * @method static Builder<static>|TransferIssue whereProcessed($value)
 * @method static Builder<static>|TransferIssue whereReason($value)
 * @method static Builder<static>|TransferIssue whereResult($value)
 * @method static Builder<static>|TransferIssue whereUpdatedAt($value)
 * @mixin Eloquent
 */
class TransferIssue extends Model
{
    use SoftDeletes;

    protected $guarded = [];
    protected $casts = [
        'phones' => 'array',
    ];
}
