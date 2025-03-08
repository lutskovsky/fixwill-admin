<?php

namespace App\Models;

use App\Integrations\RemonlineApi;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Sushi\Sushi;

/**
 *
 *
 * @property int $id
 * @property string|null $title
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RemonlineCourier newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RemonlineCourier newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RemonlineCourier query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RemonlineCourier whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RemonlineCourier whereTitle($value)
 * @mixin \Eloquent
 */
class RemonlineCourier extends Model
{
    use HasFactory;
    use Sushi;

    public function getRows()
    {
        $rem = new RemonlineApi();
        $data = $rem->getBookItems(483321)['data'];

//        return array_map(fn($item) => ['title' => $item['title']], $data);
        return $data;
    }
}
