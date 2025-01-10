<?php

namespace App\Console\Commands;

use App\Integrations\RemonlineApi;
use App\Models\Courier;
use App\Models\CourierTrip;
use Illuminate\Console\Command;

class FetchRemonlineOrders extends Command
{
    const COURIER_FIELD_PRIVOZ = 'f1482267';
    const COURIER_FIELD_OTVOZ = 'f1569113';
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
            $response = $remonline->getOrders(['statuses' => [327089, 435390, 435391, 1467781,], 'page' => ++$page]);
            if (!$response['success']) break;

            $orders = array_merge($orders, $response['data']);
        }

        $remonlineOrderIds = array_column($orders, 'id');

        foreach ($orders as $order) {
            if ($order['status']['id'] == 435391) {
                $direction = 'отвоз';
                $courierName = $order['custom_fields'][self::COURIER_FIELD_OTVOZ] ?? '';
            } else {
                $direction = 'привоз';
                $courierName = $order['custom_fields'][self::COURIER_FIELD_PRIVOZ] ?? '';
            }

            $courier = Courier::where('name', $courierName)->first();

//            if (!$courier) {
//                $this->warn("No user found for courier: {$courierName}. Skipping order_id: {$order['id']}.");
//                continue;
//            }

            $this->info("Order {$order['id']} {$courierName}");
            $data = [
                'order_label' => $order['id_label'],
                'direction' => $direction,
                'courier' => $courierName,
                'order_id' => $order['id'],
                'courier_id' => $courier->id ?? null
            ];

            $existingTrip = CourierTrip::where('order_id', $order['id'])->first();

            if ($existingTrip) {
                if (!$existingTrip->status || $existingTrip->courier != $courierName) {
                    $existingTrip->status = 'Назначен';
                }
                if (!$existingTrip->arrival_time || $existingTrip->courier != $courierName) {
                    $existingTrip->arrival_time = null;
                }

                $existingTrip->update($data);
            } else {
                $data['status'] = 'Назначен';
                CourierTrip::create($data);
            }

            $this->info("Order {$order['id']} synced successfully.");
        }

//        CourierTrip::whereNotIn('order_id', $remonlineOrderIds)->delete();

        $this->info('All orders synced.');
        return 0;
    }
}
