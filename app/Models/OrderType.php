<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderType extends Model
{
    use CrudTrait;
    use HasFactory;

    protected $fillable = [
        'type_id',
        'name',
        'operator_required',
    ];
}
