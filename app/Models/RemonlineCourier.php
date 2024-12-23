<?php

namespace App\Models;

use App\Integrations\RemonlineApi;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Sushi\Sushi;

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
