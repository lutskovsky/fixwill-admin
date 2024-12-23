<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourierTrip extends Model
{
    use HasFactory;

    protected $table = 'courier_trips';

    protected $fillable = [
        'user_id',
        'direction',
        'courier',
        'order_id',
        'arrival_time',
        'status',
    ];

    /**
     * Get the user that owns the CourierTrip.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
