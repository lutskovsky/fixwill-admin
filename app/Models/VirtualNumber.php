<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 *
 *
 * @property int $id
 * @property string $number
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-write mixed $virtual_number
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VirtualNumber newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VirtualNumber newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VirtualNumber query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VirtualNumber whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VirtualNumber whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VirtualNumber whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VirtualNumber whereNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VirtualNumber whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class VirtualNumber extends Model
{
    use CrudTrait;
    use HasFactory;

    protected $fillable = ['number', 'description'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_virtual_number');
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
