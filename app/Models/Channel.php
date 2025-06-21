<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'comagic_id'
    ];

    const TYPE_WHATSAPP = 'whatsapp';
    const TYPE_SMS = 'sms';

    public static function getTypes()
    {
        return [
            self::TYPE_WHATSAPP,
            self::TYPE_SMS,
        ];
    }

    public function isWhatsApp()
    {
        return $this->type === self::TYPE_WHATSAPP;
    }

    public function isSMS()
    {
        return $this->type === self::TYPE_SMS;
    }

    // Relationships
    public function whatsappGroups()
    {
        return $this->hasMany(Group::class, 'whatsapp_channel_id');
    }

    public function smsGroups()
    {
        return $this->hasMany(Group::class, 'sms_channel_id');
    }
}
