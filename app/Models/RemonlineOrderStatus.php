<?php

namespace App\Models;

use App\Integrations\RemonlineApi;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Sushi\Sushi;

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
