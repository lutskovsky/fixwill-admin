<?php

namespace App\Console\Commands;

use App\Integrations\RemonlineApi;
use App\Models\PotentialAlert;
use Exception;
use Illuminate\Console\Command;
use Telegram\Bot\Laravel\Facades\Telegram;

class CheckPotential extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'remonline:check-potential';

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
                    'types' => [89790, 90261, 104613, 219829],
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
            if ($order['created_at'] != $order['modified_at']) {
                continue;
            }
            $this->info('Hit! Diff: ' . ($order['modified_at'] / 1000 - time()));
            $createdAt = $order['created_at'] / 1000;
            $now = time();
            $alert = PotentialAlert::find($orderId);
            if ($alert) {
                $level = $alert->level;
            } else {
                $level = 0;
            }

            if (($createdAt < $now - 10 * 60) && ($level < 3)) {
                $msg = "🔥🔥🔥 <a href='https://web.remonline.app/orders/table/$orderId'>{$order['id_label']}</a> - 10 минут без обработки!!!";
                $level = 3;
            } elseif (($createdAt < $now - 5 * 60) && ($level < 2)) {
                $msg = "🔴 <a href='https://web.remonline.app/orders/table/$orderId'>{$order['id_label']}</a> - 5 минут без обработки";
                $level = 2;
            } elseif (($createdAt < $now - 1 * 60) && ($level < 1)) {
                $msg = "🟡 <a href='https://web.remonline.app/orders/table/$orderId'>{$order['id_label']}</a> - 1 минута без обработки";
                $level = 1;
            } else {
                continue;
            }

            PotentialAlert::updateOrCreate(
                ['order_id' => $orderId],
                ['level' => $level]
            );

            $bot->sendMessage([
                'chat_id' => config('telegram.chats.potential'),
                'text' => $msg,
                'parse_mode' => 'html']);
        }
    }
}
