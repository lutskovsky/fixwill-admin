<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 *
 *
 * @property int $id
 * @property string $name
 * @property array<array-key, mixed> $settings
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReportPreset newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReportPreset newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReportPreset query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReportPreset whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReportPreset whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReportPreset whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReportPreset whereSettings($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReportPreset whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ReportPreset extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'settings'];

    protected $casts = [
        'settings' => 'array'
    ];
}
