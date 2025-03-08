<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 *
 *
 * @property int $id
 * @property string $name
 * @property string|null $internal_phone
 * @property string|null $tg_login
 * @property string|null $chat_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $sip_line_id
 * @property-read \App\Models\SipLine|null $sipLine
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Courier newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Courier newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Courier query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Courier whereChatId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Courier whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Courier whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Courier whereInternalPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Courier whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Courier whereSipLineId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Courier whereTgLogin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Courier whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Courier extends Model
{
    use CrudTrait;
    use HasFactory;

    protected $table = 'couriers';

    protected $fillable = [
        'name',
        'internal_phone',
        'tg_login',
        'chat_id',
        'sip_line_id',
    ];

    public function sipLine(): BelongsTo
    {
        return $this->belongsTo(SipLine::class);
    }
}
