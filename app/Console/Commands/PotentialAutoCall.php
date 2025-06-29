<?php

namespace App\Console\Commands;

use App\Http\Controllers\EmployeeCallController;
use App\Integrations\RemonlineApi;
use App\Models\PotentialAlert;
use App\Models\PotentialCall;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;

class PotentialAutoCall extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'remonline:potential-autocall';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $remonline = new RemonlineApi();

        $bot = Telegram::bot('notifications');
        $orders = [];
        $page = 0;
        while (true) {
            try {
                $response = $remonline->getOrders([
                    'statuses' => [296500],
                    'types' => [89790, 104613, 219829],
                    'page' => ++$page
                ]);
                $orders = array_merge($orders, $response['data']);
            } catch (Exception $e) {
                break;
            }
        }

        foreach ($orders as $order) {
            $orderId = $order['id'];
            $this->info("$orderId {$order['created_at']} {$order['modified_at']}");

            // Check for call
            if (Carbon::createFromTimestamp($order['created_at'] / 1000)->gt(Carbon::now()->subMinutes(10))) {
                continue;
            }

            $potentialCall = PotentialCall::find($orderId);

            if (!$potentialCall ||
                ($potentialCall->latest_call && $potentialCall->latest_call->lt(Carbon::now()->subMinutes(10))) ||
                !$potentialCall->latest_call) {

                $phone = $order['client']['phone'][0];
                if ($phone) {
                    //EmployeeCallController::executeScenarioCall($phone, config('services.comagic.potential_call_scenario_id'));

                    PotentialCall::updateOrCreate(
                        ['id' => $orderId],
                        ['latest_call' => now()]

                    );
                }
            }
        }

        $currentPotentialOrderIds = array_column($orders, 'id');
        PotentialCall::whereNotIn('id', $currentPotentialOrderIds)->delete();
    }
}
