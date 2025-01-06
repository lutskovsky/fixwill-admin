<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    ];
}
