<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;

class EquipmentAlias extends Model
{
    use CrudTrait;

    protected $fillable = [
        'target',
        'alias',
    ];
}
