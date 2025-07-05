<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;
    use CrudTrait;


    protected $fillable = [
        'name',
        'whatsapp_channel_id',
        'sms_channel_id',
        'tg_chat_id',
    ];

    // Relationships
    public function whatsappChannel()
    {
        return $this->belongsTo(Channel::class, 'whatsapp_channel_id');
    }

    public function smsChannel()
    {
        return $this->belongsTo(Channel::class, 'sms_channel_id');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    // Helper methods
    public function hasWhatsAppChannel()
    {
        return !is_null($this->whatsapp_channel_id);
    }

    public function hasSMSChannel()
    {
        return !is_null($this->sms_channel_id);
    }
}
