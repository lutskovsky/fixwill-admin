<?php

namespace App\Models;

use App\Integrations\RemonlineApi;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Sushi\Sushi;

/**
 *
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $color
 * @property int|null $group
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RemonlineOrderStatus newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RemonlineOrderStatus newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RemonlineOrderStatus query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RemonlineOrderStatus whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RemonlineOrderStatus whereGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RemonlineOrderStatus whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RemonlineOrderStatus whereName($value)
 * @mixin \Eloquent
 */
class RemonlineOrderStatus extends Model
{
    use Sushi;

    // Cache duration in seconds (10 minutes)
    protected $cacheDuration = 600;

    public function getRows()
    {
        return Cache::remember('remonline_statuses', $this->cacheDuration, function () {
            $rem = new RemonlineApi();
            return $rem->getStatuses()['data'];
        });
    }

    // Optional: Add method to clear cache when needed
    public static function clearCache()
    {
        Cache::forget('remonline_statuses');
    }
}
