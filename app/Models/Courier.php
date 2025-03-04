<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
