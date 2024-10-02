<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VirtualNumber extends Model
{
    use CrudTrait;
    use HasFactory;

    protected $fillable = ['number', 'description'];

    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'employee_virtual_number');
    }


    public function setVirtualNumberAttribute($value)
    {
        $this->attributes['number'] = $this->sanitizePhone($value);
    }

    /**
     * @param $value
     * @return array|string|string[]|null
     */
    protected function sanitizePhone($value): string|array|null
    {
        return preg_replace('/\D/', '', $value);
    }
}
