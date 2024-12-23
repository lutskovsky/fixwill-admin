<?php

namespace App\Console\Commands;

use App\Integrations\RemonlineApi;
use App\Models\CourierTrip;
use App\Models\User;
use Illuminate\Console\Command;

class FetchRemonlineOrders extends Command
{
/**
     * The name of the custom field in the order array that contains courier info.
     * Adjust this constant to the actual field name from the Remonline API.
     */
    const COURIER_FIELD = 'f1482267';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'remonline:fetch-orders';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch orders from Remonline API and sync them into CourierTrips'; // Example field name

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Fetching orders from Remonline...');

        $remonline = new RemonlineApi();

        $orders = [];
        $page = 0;
        while (true) {
            $response = $remonline->getOrders(['statuses' => [323217], 'page' => ++$page]);
            if (!$response['success']) break;

            $orders = array_merge($orders, $response['data']);
        }

        // 2. Process each order
        foreach ($orders as $order) {
            // Extract the courier name from the order custom fields
            $courierName = $order['custom_fields'][self::COURIER_FIELD] ?? null;
            if (!$courierName) continue;

            $user = User::where('remonline_courier', $courierName)->first();
            if (!$user) {
                $this->warn("No user found for courier: {$courierName}. Skipping order_id: {$order['id']}.");
                continue;
            }

            $this->info("Order {$order['id']} {$courierName}");
            // Prepare data to insert/update
            $data = [
                'user_id' => $user->id,
                'direction' => '',
                'courier' => $courierName,
                'arrival_time' => null,
                'status' => 'pending',
            ];

            // 3. Update or create the CourierTrip
            CourierTrip::updateOrCreate(
                ['order_id' => $order['id']],
                $data
            );

            $this->info("Order {$order['id']} synced successfully.");
        }

        $this->info('All orders synced.');
        return 0;
    }
}
