<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PotentialCall extends Model
{
    protected $fillable = ['id', 'latest_call'];

    protected $casts = [
        'latest_call' => 'datetime',
    ];

    public $incrementing = false;
    protected $keyType = 'int';
}
