<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportPreset extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'settings'];

    protected $casts = [
        'settings' => 'array'
    ];
}
