<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 *
 *
 * @property int $id
 * @property string $name
 * @property string|null $internal_phone
 * @property string|null $remonline_login
 * @property string|null $tg_login
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $chat_id
 * @property-read string $virtual_numbers_list
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\VirtualNumber> $virtualNumbers
 * @property-read int|null $virtual_numbers_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereChatId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereInternalPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereRemonlineLogin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereTgLogin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Employee extends Model
{
    use CrudTrait;
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'employees';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    // protected $fillable = [];
    // protected $hidden = [];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function virtualNumbers()
    {
        return $this->belongsToMany(VirtualNumber::class, 'employee_virtual_number');
    }
    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    /**
     * Accessor to get virtual numbers as a comma-separated string.
     *
     * @return string
     */
    public function getVirtualNumbersListAttribute()
    {
         return $this->virtualNumbers->pluck('number')->implode(', ');
    }

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */

    public function setTgLoginAttribute($value)
    {
        $this->attributes['tg_login'] = $this->sanitizePhone($value);
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
