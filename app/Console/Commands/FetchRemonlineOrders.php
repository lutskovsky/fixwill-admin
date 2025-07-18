<?php

namespace App\Console\Commands;

use App\Http\Controllers\TelegramBots\LogisticsBotController;
use App\Integrations\RemonlineApi;
use App\Models\Courier;
use App\Models\CourierTrip;
use Exception;
use Illuminate\Console\Command;

class FetchRemonlineOrders extends Command
{
    const COURIER_FIELD_PRIVOZ = 'f1482267';
    const COURIER_FIELD_OTVOZ = 'f1569113';
    const COURIER_TYPE_FIELD_PRIVOZ = 'f1620346';
    const COURIER_TYPE_FIELD_OTVOZ = 'f5171042';
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
            try {
                $response = $remonline->getOrders(['statuses' => [327089, 435390, 435391, 1467781,], 'page' => ++$page]);
                $orders = array_merge($orders, $response['data']);
            } catch (Exception $e) {
                break;
            }
        }

        foreach ($orders as $order) {
            if ($order['id'] != 50906446) {
                continue;
            }

            if ($order['status']['id'] == 435391) {
                $direction = 'отвоз';
                $courierName = $order['custom_fields'][self::COURIER_FIELD_OTVOZ] ?? '';
                $date = ($order['client']['custom_fields']['f1569111'] ?? 'нет');
            } else {
                $direction = 'привоз';
                $courierName = $order['custom_fields'][self::COURIER_FIELD_PRIVOZ] ?? '';
                $date = ($order['client']['custom_fields']['f1482265'] ?? 'нет');
            }

            if ($date != 'нет') {
                $date = date('d.m.Y', $date / 1000);
            }

            $courier = Courier::where('name', $courierName)->first();
            if (!$courier) {
                continue;
            }

            $this->info("Order {$order['id']} {$courierName}");

            $existingTrip = CourierTrip::current()
                ->where('order_id', $order['id'])
                ->where('moved_on', false)
                ->where('courier', $courierName)
//                ->where('date', $date)
                ->first();

            if (!$existingTrip) {

                $this->info("creating new trip");
                $data = [
                    'order_label' => $order['id_label'],
                    'status' => 'Назначен',
                    'direction' => $direction,
                    'courier' => $courierName,
                    'date' => $date,
                    'order_id' => $order['id'],
                    'courier_id' => $courier->id ?? null,
                    'courier_type' => $order['custom_fields']['f1620346'] ?? '',
                ];

                CourierTrip::create($data);

                if ($courier && $courier->chat_id) {
                    $bot = new LogisticsBotController($courier->chat_id);
                    $bot->showTripDetails($order['id'], true);
                }
            }
            else {
                if (!$existingTrip->date) {
                    $existingTrip->date = $date;
                    $existingTrip->save();
                }
            }
        }

        CourierTrip::whereNotIn('order_id', array_column($orders, 'id'))->update(['moved_on' => true]);

        $this->info('All orders synced.');
        return 0;
    }
}
