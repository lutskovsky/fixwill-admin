<?php

namespace App\Listeners;

use App\Events\StatusChanged;
use App\Integrations\RemonlineApi;
use App\Models\Status;
use App\Models\TransferIssue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Telegram\Bot\Laravel\Facades\Telegram;


class TransferIssueNotification
{
    private Api $bot;
    private RemonlineApi $remonline;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        $this->bot = Telegram::bot('status');
        $this->remonline = new RemonlineApi();
    }

    /**
     * Handle the event.
     */
    public function handle(StatusChanged $event): void
    {
        $oldStatus = $event->statusChange->old_status_id;
        $oldStatus = Status::where("status_id", $oldStatus)->first();
        $newStatus = $event->statusChange->new_status_id;
        $newStatus = Status::where("status_id", $newStatus)->first();

        if ($oldStatus->reschedule_before && $newStatus->reschedule_after) {
            $issueType = 'reschedule';
            $chat = config('telegram.chats.reschedule');
        } elseif ($oldStatus->refusal_before && $newStatus->refusal_after) {
            $issueType = 'refusal';
            $chat = config('telegram.chats.refusal');
        } else {
            return;
        }
//
        $orderId = $event->statusChange->order_id;
//
//        $order = $this->remonline->getOrderById($orderId);


        $order = $event->newData;

        $oldDate = $event->oldData['custom_fields']['f1482265'] ?? 0;
        $oldDate = $oldDate ? RemonlineApi::convertDate($oldDate) : 'не задано';

        $newDate = $order['custom_fields']['f1482265'] ?? 0;
        $newDate = $newDate ? RemonlineApi::convertDate($newDate) : 'не задано';

        if ($oldDate == $newDate) {
            $dateText = "$newDate (не менялась)";
        } else {
            $dateText = "$oldDate → $newDate";
        }

        $link = "<a href='https://web.remonline.app/orders/table/$orderId'>{$order['id_label']}</a>";
        $equipmentType = $order['custom_fields']['f1070009'] ?? 'не задано';
        $diag = $order['custom_fields']['f1536267'] ?? 'не задано';
        $brand = $order['custom_fields']['f1070012'] ?? 'не задано';
        $courierType = $order['custom_fields']['f1620346'] ?? 'не задано';
        $courier = $order['custom_fields']['f1482267'] ?? 'не задано';
        $city = $order['custom_fields']['f5192512'] ?? 'не задано';
        $description = "{$link}\n" .
            "Тип изделия: {$equipmentType}\n" .
            "Диагональ: {$diag}\n" .
            "Бренд: {$brand}\n" .
            "Тип курьера: {$courierType}\n" .
            "Курьер: {$courier}\n" .
            "Дата привоза: $dateText\n" .
            "Город: {$city}\n";

        $buttons = [
            [[
                'text' => "❓ Указать причину",
                'switch_inline_query_current_chat' => "reason:$orderId\n\n",
            ]],
            [[
                'text' => "✔️ Обработан",
                'switch_inline_query_current_chat' => "process:$orderId\n\n",
            ]],
            [[
                'text' => "🌙 До конца дня",
                'callback_data' => "postpone:$orderId",
            ]],
        ];
        $replyMarkup = ['inline_keyboard' => $buttons];

        $this->bot->sendMessage(['chat_id' => $chat, 'text' => $description, 'parse_mode' => 'html', 'reply_markup' => json_encode($replyMarkup)]);

        TransferIssue::create(
            [
                'order_id' => $orderId,
                'type' => $issueType,
                'phones' => $order['client']['phone'] ?? [],
                'description' => $description,
            ]
        );
    }

    public function getMessage(Request $request)
    {

//        if (isset($data['callback_query'])) {
//            $this->handleCallback($data);
//        }

        $data = $request->all();

//        Log::info(print_r($data, true));

        if (isset($data['callback_query'])) {
            $callbackQuery = $data['callback_query'];
            $callbackQueryId = $callbackQuery['id'];
            $data = $callbackQuery['data'] ?? null;
            $chatId = $callbackQuery['message']['chat']['id'];

            if (!$data) {
                return;
            }
            [$action, $orderId] = explode(':', $data);

            if ($action == 'postpone') {
                try {
                    $this->bot->answerCallbackQuery([
                        'callback_query_id' => $callbackQueryId,
                        'text' => "Проверка отложена до 21:00"
                    ]);
                } catch (TelegramSDKException $e) {
                    $this->bot->sendMessage(['chat_id' => $chatId, 'text' => "Проверка отложена до 21:00"]);
                }

                $issue = TransferIssue::where('order_id', $orderId)->first();
                if (!$issue) {
                    return;
                }

                $issue->postponed = true;
                $issue->save();
            }
            return;
        }


        $message = $data['message'] ?? null;


        $text = $message['text'] ?? '';
        $chatId = $message['chat']['id'] ?? null;
        $firstName = $message['from']['first_name'] ?? '';
        $lastName = $message['from']['last_name'] ?? '';
        $username = $message['from']['username'] ?? '';
        $submitter = "$firstName $lastName @$username";

        $lines = explode("\n", $text, 2);
        $firstLine = $lines[0];
        $reply = isset($lines[1]) ? trim($lines[1]) : '';


        $matches = [];
        if (preg_match('/@fixwill_status_bot\s+(\w+):(\d+)/', $firstLine, $matches)) {
            $action = $matches[1] ?? null;
            $orderId = $matches[2] ?? null;
            if (!$action || !is_numeric($orderId)) {
                return;
            }

            $issue = TransferIssue::where('order_id', $orderId)->first();
            if (!$issue) {
                return;
            }

            if ($action == 'reason') {
                $issue->reason = $reply;
            }
            if ($action == 'process') {
                $issue->result = "$submitter\n$reply";
                $issue->processed = true;
            }

            $issue->save();
        }
    }
}

;
