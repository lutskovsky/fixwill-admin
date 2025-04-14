<?php

namespace App\Console\Commands;

use App\Integrations\RemonlineApi;
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
            $this->info("{$order['id']} {$order['created_at']} {$order['modified_at']}");
            if ($order['created_at'] != $order['modified_at']) {
                continue;
            }
            $createdAt = $order['created_at'] / 1000;
            $now = time();
            if ($createdAt < $now - 10 * 60) {
                $msg = "🔥🔥🔥 <a href='https://web.remonline.app/orders/table/{$order['id']}'>{$order['id_label']}</a> - 10 мин";
            } elseif ($createdAt < $now - 5 * 60) {
                $msg = "🔴 <a href='https://web.remonline.app/orders/table/{$order['id']}'>{$order['id_label']}</a> - 5 мин";
            } elseif ($createdAt < $now - 1 * 60) {
                $msg = "🟡 <a href='https://web.remonline.app/orders/table/{$order['id']}'>{$order['id_label']}</a> - 1 мин";
            } else {
                continue;
            }

            $bot->sendMessage([
                'chat_id' => config('telegram.chats.potential'),
                'text' => $msg,
                'parse_mode' => 'html']);
        }
    }
}
