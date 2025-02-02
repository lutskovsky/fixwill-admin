<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    use CrudTrait;
    use HasFactory;

    protected $fillable = [
        'status_id',
        'status_name',
        'current',
        'accepted_by_operator',
        'success_for_operator',
        'transit',
    ];
}
