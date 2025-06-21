<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'visitor_phone',
        'channel_id',
    ];

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function scopeByPhoneAndChannel($query, $phone, $channelId)
    {
        return $query->where('visitor_phone', $phone)
            ->where('channel_id', $channelId);
    }
}
