<?php

namespace App\Models;

use App\Integrations\RemonlineApi;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Sushi\Sushi;

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
