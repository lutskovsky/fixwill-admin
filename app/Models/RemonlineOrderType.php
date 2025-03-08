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
 * @property string|null $name
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RemonlineOrderType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RemonlineOrderType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RemonlineOrderType query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RemonlineOrderType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RemonlineOrderType whereName($value)
 * @mixin \Eloquent
 */
class RemonlineOrderType extends Model
{
    use HasFactory;
    use Sushi;

    public function getRows()
    {
        $rem = new RemonlineApi();
        return $rem->getOrderTypes()['data'];
    }
}
