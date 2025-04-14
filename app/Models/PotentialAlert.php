<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PotentialAlert extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $primaryKey = 'order_id';
    protected $fillable = [
        'order_id',
        'level',
    ];

    protected $casts = [
        'order_id' => 'integer',
        'level' => 'integer',
    ];
}
