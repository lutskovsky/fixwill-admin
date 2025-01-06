<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourierTrip extends Model
{
    use HasFactory;

    protected $table = 'courier_trips';

    protected $fillable = [
        'courier_id',
        'direction',
        'courier',
        'order_id',
        'arrival_time',
        'status',
        'order_label',
    ];

    /**
     * Get the courier that owns the CourierTrip.
     */
    public function courier()
    {
        // references the 'courier_id' column in 'courier_trips'
        return $this->belongsTo(Courier::class, 'courier_id');
    }
}
