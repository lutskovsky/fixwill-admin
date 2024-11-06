<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use CrudTrait;
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'password',
        'internal_phone',
        'tg_login',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


    public function virtualNumbers()
    {
        return $this->belongsToMany(VirtualNumber::class, 'user_virtual_number');
    }


    public function getVirtualNumbersListAttribute()
    {
        return $this->virtualNumbers->pluck('number')->implode(', ');
    }


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
