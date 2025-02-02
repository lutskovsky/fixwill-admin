<?php

namespace App\Console\Commands;

use App\Models\RemonlineOrderStatus;
use App\Models\Status;
use Illuminate\Console\Command;

class SyncRemonlineStatuses extends Command
{
    protected $signature = 'statuses:sync';
    protected $description = 'Sync statuses with Remonline API';

    public function handle()
    {
        // Get current API statuses
        $apiStatuses = RemonlineOrderStatus::all()->keyBy('id');


        // Add/Update existing statuses
        foreach ($apiStatuses as $apiStatus) {

            $updateArray = ['status_name' => $apiStatus->name];

            $existingStatus = Status::where('status_id', $apiStatus->id)->first();

            if (true || !$existingStatus) {
                if (!in_array($apiStatus->group, [1, 6, 7])) {
                    $updateArray['current'] = true;
                } else {
                    $updateArray['current'] = false;
                }
            }

            Status::updateOrCreate(
                ['status_id' => $apiStatus->id],
                $updateArray
            );
        }

        // Delete statuses not present in API
        $idsToKeep = $apiStatuses->pluck('id')->toArray();
        Status::whereNotIn('status_id', $idsToKeep)->delete();

        $this->info('Synced ' . count($apiStatuses) . ' statuses.');
    }
}
