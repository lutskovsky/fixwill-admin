<?php

namespace App\Console\Commands;

use App\Models\OrderType;
use App\Models\RemonlineOrderType;
use Illuminate\Console\Command;

class SyncRemonlineOrderTypes extends Command
{
    protected $signature = 'order_types:sync';
    protected $description = 'Sync types with Remonline API';

    public function handle()
    {
        $apiTypes = RemonlineOrderType::all()->keyBy('id');
        foreach ($apiTypes as $apiType) {

            OrderType::updateOrCreate(
                ['type_id' => $apiType->id],
                ['name' => $apiType->name]
            );
        }

        $idsToKeep = $apiTypes->pluck('id')->toArray();
        OrderType::whereNotIn('type_id', $idsToKeep)->delete();
    }
}
