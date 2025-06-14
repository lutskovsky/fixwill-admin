<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'chat_id',
        'text',
        'source',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }
}
