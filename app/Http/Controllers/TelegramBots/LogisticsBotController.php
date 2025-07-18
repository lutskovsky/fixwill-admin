<?php

namespace App\Http\Controllers\TelegramBots;

use App\Http\Controllers\Controller;
use App\Http\Controllers\EmployeeCallController;
use App\Integrations\RemonlineApi;
use App\Models\Courier;
use App\Models\CourierTrip;
use App\Services\Telegram\TelegramBotService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;


class LogisticsBotController extends Controller
{
    const MANAGERS_CHAT = -1002214584408;
    protected TelegramBotService $botService;
    protected string|null $mode = null;
    protected string|null $chatId = null;

    public function __construct($chatId = null)
    {
        $this->chatId = $chatId;

        // Retrieve the token for the logistics bot
        $token = config('telegramBots.logistics');
        // Or: $token = env('TELEGRAM_BOT_TOKEN_LOGISTICS');
        $this->botService = new TelegramBotService($token);
    }

    protected function getLabel($id, $label)
    {
        if ($this->mode == 'manager') {
            return "<a href='https://web.remonline.app/orders/table/$id'>$label</a>";
        } else {
            return $label;
        }
    }

    public function handle(Request $request)
    {
        $data = $request->all();
        Log::channel('telegram')->info($data);

        if (isset($data['callback_query'])) {
            $this->handleCallback($data);
            return response('OK', 200);
        }

        $message = $data['message']['text'] ?? null;
        $this->chatId = $data['message']['chat']['id'] ?? null;
        $contact = $data['message']['contact'] ?? null;
        $username = $data['message']['from']['username'] ?? null;

        if ($this->chatId == self::MANAGERS_CHAT) {
            $this->mode = 'manager';
            $courier = null;
        } else {
            $this->mode = 'courier';

            if ($message === '/start') {
                $this->botService->requestPhoneNumber($this->chatId);
                return response('OK', 200);
            }

            if ($contact) {
                $phoneNumber = $contact['phone_number'];
                $this->botService->processPhoneNumber($phoneNumber, $this->chatId, 'Courier');
                return response('OK', 200);
            }

            if ($this->chatId) {
                $courier = Courier::where('chat_id', $this->chatId)->first();
                if (!$courier) {
                    $this->botService->sendMessage($this->chatId, "Не знаю, кто вы, пожалуйста, отправьте /start и поделитесь номером.");
                    return response('OK', 200);
                }
            }
        }

        if ($message && preg_match('/^(.+)@fixwill.*$/', $message, $matches)) {
            $message = $matches[1];
        }

        if ($message && preg_match('/\/info:(\d+):(.+)\n([\S\s]*)/', $message, $matches)) {

            $orderId = $matches[1];
            $status = $matches[2];
            $resultMsg = $matches[3];

            $trip = CourierTrip::where('order_id', $orderId)->orderBy('id', 'DESC')->first();

            $trip->update(['result' => $resultMsg, 'active' => false]);

            $this->applyChange('set_status', "set_status:$orderId:$status", $trip);
        }


        if ($message === '/active') {
            // ... Show short list of user's trips ...
            $this->listAllTripsShort($courier, true);
            return response('OK', 200);
        }

        if ($message === '/history') {
            return;
            // ... Show short list of user's trips ...
            $this->listAllTripsShort($courier, false);
            return response('OK', 200);
        }

        if ($message && preg_match('/^\/order_(\d+)$/', $message, $matches)) {
            $orderId = $matches[1];
            $this->showTripDetails($orderId);
            return response('OK', 200);
        }


//        if ($this->mode == 'courier') {
//            $messageToManagers = "Сообщение от {$courier->name}";
//            if ($username)
//                $messageToManagers .= " (@$username)";
//
//            $messageToManagers .= ": \n" . $message;
//
//            $this->botService->sendMessage(self::MANAGERS_CHAT, $messageToManagers);
//        }
        return response('OK', 200);
    }

    /**
     * Show a short list of the user’s trips in one message.
     */
    protected function listAllTripsShort(Courier|null $courier, $onlyActive)
    {
        if ($courier) {
            if ($onlyActive) {
                $trips = CourierTrip::where('courier_id', $courier->id)
                    ->where('active', true)
                    ->where('moved_on', false)
                    ->get();
            } else {
                $trips = CourierTrip::where('courier_id', $courier->id)
                    ->where('active', false)
                    ->get();

            }

            $messageText = "Ваши заказы:\n";
            foreach ($trips as $trip) {
                try {
                    $remonline = new RemonlineApi();
                    $order = $remonline->getOrderById($trip->order_id);
                } catch (Exception $e) {
                    $this->sendMsg($e->getMessage());
                    return;
                }
                $address = $order['client']['address'];

                $messageText .= "\nЗаказ {$trip->order_label} ({$trip->direction}) - {$trip->status}\n";
                $messageText .= "$address\n";
                $messageText .= "Подробнее: /order_{$trip->order_id}\n";

                if (mb_strlen($messageText) > 4050) {
                    $this->botService->sendMessage($this->chatId, $messageText);
                    $messageText = "";
                }
            }
        } elseif ($this->chatId == self::MANAGERS_CHAT) {
            $messageText = "";


            if ($onlyActive) {
                $trips = CourierTrip::where('active', true)
                    ->where('moved_on', false)
                    ->get();
            } else {
                $trips = CourierTrip::where('active', false)
                    ->get();

            }

            $couriers = $trips->groupBy('courier');

            foreach ($couriers as $courier => $trips) {
                $courierText = "- $courier:\n";
                foreach ($trips as $trip) {
                    $courierText .= $this->getLabel($trip->order_id, $trip->order_label) . " ({$trip->direction}) - {$trip->status} /order_{$trip->order_id}\n";

                    if (mb_strlen($messageText . $courierText) > 4050) {
                        $this->botService->sendMessage($this->chatId, $messageText . $courierText);
                        $messageText = "";
                        $courierText = "";
                    }

                }

                if (mb_strlen($messageText . $courierText) > 4096) {
                    $this->botService->sendMessage($this->chatId, $messageText);
                    $messageText = "";
                }
                $messageText .= $courierText . "\n";
            }

        } else {
            return;
        }

        if ($trips->isEmpty()) {
            $this->botService->sendMessage($this->chatId, "Нет заказов");
            return;
        }
        if ($messageText) {
            $this->botService->sendMessage($this->chatId, $messageText);
        }
    }

    /**
     * Show detailed info about a single trip, plus inline buttons.
     */
    public function showTripDetails($orderId, $new = false)
    {
        if ($new) {
            $this->mode = 'courier';
        }

        try {
            $remonline = new RemonlineApi();
            $order = $remonline->getOrderById($orderId);
        } catch (Exception $e) {
            $this->sendMsg($e->getMessage());
            return;
        }

        $trip = CourierTrip::where('order_id', $orderId)->orderBy('id', 'DESC')->first();

        if (!$trip) {
            $this->botService->sendMessage($this->chatId, "Заказ не найден.");
            return;
        }

        $warning = $trip->courier_id ? '' : " <b>(не пользуется ботом!)</b>";

        $priority = $order['custom_fields']['f1617065'] ?? null;

        $text = "";

        if ($priority) $text .= "!!!ПРИОРИТЕТНЫЙ ЗАКАЗ, ЗАБРАТЬ В ЛЮБОМ СЛУЧАЕ, НЕ ПЕРЕНОСИТЬ!!!\n";

        $text .= "<b>" . mb_strtoupper($trip->direction) . "</b>\n";
        $text .= "Заказ " . $this->getLabel($orderId, $order['id_label']) . "\n";

        $text .= "Курьер: {$trip->courier}$warning\n";
        $text .= "Тип: {$trip->courier_type}\n";
        $text .= "Этап: {$trip->status}\n";


        $date = $trip->direction == "привоз"
            ? ($order['custom_fields']['f1482265'] ?? 'нет')
            : ($order['client']['custom_fields']['f1569111'] ?? 'нет');

        if ($date != 'нет') {
            $date = date('d.m.Y', $date / 1000);
        }
        $text .= "Дата: $date\n";


        if ($trip->direction == "привоз") {
//            $text .= "Дата: " . ($order['custom_fields']['f1482265'] ?? '') . "\n";
            $text .= "Интервал от клиента: " . ($order['custom_fields']['f4903156'] ?? '') . "\n";
            $text .= "Перенос интервала: {$trip->arrival_time}\n";
        }

        $text .= "---\n";

        $name = $order['client']['name'] ?? ($order['client']['first_name'] . ' ' . $order['client']['last_name']);
        $text .= "Клиент: {$name}\n";
        $text .= "Адрес: {$order['client']['address']}\n";
        $entrance = $order['client']['custom_fields']['f3532128'] ?? "";
        $text .= "Подъезд: {$entrance}\n";
        $floor = $order['client']['custom_fields']['f3532129'] ?? '';
        $text .= "Этаж: {$floor}\n";
        $flat = $order['client']['custom_fields']['f3532130'] ?? '';
        $text .= "Квартира: {$flat}\n";
        $metro = $order['client']['custom_fields']['f3452769'] ?? '';
        $text .= "Метро: {$metro}\n";
        $equipment = $order['custom_fields']['f1070009'] ?? '';
        $text .= "Оборудование: {$equipment}\n";
        $brand = $order['custom_fields']['f1070012'] ?? '';
        $text .= "Бренд: {$brand}\n";
        $diagonal = $order['custom_fields']['f1536267'] ?? '';
        $text .= "Диагональ: {$diagonal}\n";

        if ($trip->direction == "привоз") {
            $fault = $order['custom_fields']['f1078980'] ?? '';
            $text .= "Неисправность: {$fault}\n";
            $comment = $order['custom_fields']['f1482266'] ?? '';
            $text .= "Примечание: {$comment}\n";
            $paid = $order['custom_fields']['f8547733'] ?? '';
            $text .= "Платный привоз: {$paid}\n";
            $site = $order['custom_fields']['f4196099'] ?? '';
            $text .= "Сайт: {$site}\n";
        } else {
            $comment = $order['custom_fields']['f2045047'] ?? '';
            $text .= "Примечание: {$comment}\n";
            $text .= "Предоплачено: {$order['payed']}\n";
            $toPay = $order['price'] - $order['payed'];
            $text .= "К оплате: {$toPay}\n";
        }

        if ($result = $trip->result) {
            $text .= $result;
        }

        $text = "<blockquote expandable>$text</blockquote>";

        if ($new) {
            $text = "Новый {$trip->direction}!\n" . $text;
        }

        $inlineKeyboard = $this->getInlineKeyboard($trip);

        if ($this->mode == 'courier') {
            $phones = array_map(
                fn($phone) => [[
                    'text' => '📞 ' . substr($phone, 0, 7) . '****',
                    'callback_data' => "call:" . $phone
                ]],
                $order['client']['phone']);
            $inlineKeyboard = array_merge($inlineKeyboard, $phones);
        }

        $replyMarkup = ['inline_keyboard' => $inlineKeyboard];
        $this->botService->sendMessage($this->chatId, $text, $replyMarkup);
    }

    /**
     * Handle callback queries (inline button presses).
     */
    protected function handleCallback(array $update)
    {
        $callbackQuery = $update['callback_query'];
        $callbackQueryId = $callbackQuery['id'];
        $data = $callbackQuery['data'] ?? null;
        $this->chatId = $callbackQuery['message']['chat']['id'];
        $messageId = $callbackQuery['message']['message_id'];

        if (!$data) {
            return;
        }

        [$action, $orderId] = explode(':', $data);
        $trip = CourierTrip::where('order_id', $orderId)->orderBy('id', 'DESC')->first();

        if ($action == 'call') {

            $this->call($data, $callbackQueryId);

//            $notificationBot = new TelegramBotService(config('telegramBots.notifications'));
//            $notificationBot->sendMessage("-4687255586");
            return;
        }

        if (!$trip) {
            // You might want to answerCallbackQuery here
            $this->botService->sendMessage($this->chatId, "Заказ не найден :(");
            return;
        }

        switch ($action) {
            case 'change_arrival':
                $this->showArrivalOptions($orderId);
                break;

            case 'set_status':
            case 'set_arrival':
            $this->applyChange($action, $data, $trip);
                break;
            case 'success':
            case 'fail':
            $this->complete($action, $data, $trip);

        }
    }

    protected function call($data, $callbackQueryId)
    {
        $parts = explode(':', $data);
        if (count($parts) < 2) {
            return;
        }

        $phone = $parts[1];

        if (Cache::has('call_cooldown_' . $this->chatId)) {
            $this->sendMsg("Не чаще одного звонка в 5 секунд!");

            return response('OK', 200);

        } else {
            Cache::put('call_cooldown_' . $this->chatId, true, 5);
            try {
                $result = EmployeeCallController::courierCall($phone, $this->chatId);
                $sessionId = $result['result']['data']['call_session_id'];
                Cache::put('call_session_' . $sessionId, $this->chatId, 5 * 60);
            } catch (Exception $e) {
                $this->sendMsg("Не получилось запустить звонок: " . $e->getMessage());

                return response('OK', 200);
            }

            $this->botService->answerCallbackQuery($callbackQueryId, "Звонок запущен, ждите. Session ID $sessionId");

            return response('OK', 200);
//            $this->sendMsg("Звонок запущен, ждите. Session ID $sessionId");
        }
    }

    protected function showArrivalOptions($orderId)
    {
        $arrivalIntervals = ['9-12', '12-15', '15-18', '18-21'];
        $buttons = [];
        foreach ($arrivalIntervals as $interval) {
            $buttons[] = [[
                'text' => $interval,
                'callback_data' => "set_arrival:$orderId:$interval"
            ]];
        }

        $replyMarkup = ['inline_keyboard' => $buttons];
        $this->botService->sendMessage($this->chatId, 'Выберите интервал:', $replyMarkup);
    }

    /**
     * Apply the chosen status or arrival interval to the trip.
     */
    protected function applyChange($action, $data, CourierTrip $trip)
    {
        // data is like "set_status:delivered:123" or "set_arrival:9-12:123"
        $parts = explode(':', $data);
        if (count($parts) < 3) {
            return;
        }

        $value = $parts[2]; // e.g. "delivered" or "9-12"

        if ($action === 'set_status') {
            $trip->update(['status' => $value]);
            $msg = $this->getLabel($trip->order_id, $trip->order_label) . "  $value.";

        } elseif ($action === 'set_arrival') {
            $trip->update(['arrival_time' => $value]);
            $msg = "⏱️ Время заказа " . $this->getLabel($trip->order_id, $trip->order_label) . " изменено на $value.";
        }


        if (isset($msg)) {
            $msg .= "\n/order_{$trip->order_id}";
            $this->botService->sendMessage($this->chatId, $msg);
            if ($this->chatId !== self::MANAGERS_CHAT) {
                $this->botService->sendMessage(self::MANAGERS_CHAT, $msg);
            }
        }
    }

    protected function getInlineKeyboard($trip)
    {
        $orderId = $trip->order_id;
        $buttons = [];

//        if (!$trip->active) {
//            return $buttons;
//        }

//        if ($this->mode == 'manager') {
//            $buttons[] = [[
//                        'text' => "Сменить этап",
//                        'callback_data' => "change_status:$orderId"
//                    ],
//                    [
//                        'text' => "Сменить интервал",
//                        'callback_data' => "change_arrival:$orderId"
//                    ]];
//            return $buttons;
//        }


        if ($trip->direction == 'привоз') {
            if (!$trip->arrival_time) {
                $arrivalIntervals = ['9-12', '12-15', '15-18', '18-21'];
//                $buttons[] = [[
//                    'text' => "Уточните интервал:",
//                ]];
                foreach ($arrivalIntervals as $interval) {
                    $buttons[] = [[
                        'text' => $interval,
                        'callback_data' => "set_arrival:$orderId:$interval"
                    ]];
                }
                return $buttons;
            }

            $options = [
                '✅ Забрал' => "success:$orderId:✅ Забрал"
            ];

            if ($trip->courier_type == 'мастер') {
                $options['💵 Взял >1000, не забрал'] = null;
            }
            $options['⏱️ Перенести время'] = "change_arrival:$orderId";
            $options['❌ Отказ'] = "fail:$orderId:❌ Отказ";


        } else {
            $options = [
                '✅ Отдал товар' => "success:$orderId:✅ Отдал товар",
//                'Перенести время' => "change_arrival:$orderId",
                '⚠️ Проблемная доставка' => "fail:$orderId:⚠️ Проблемная доставка"
            ];
        }

        foreach ($options as $status => $callback) {
            if (!$callback) {
                $callback = "set_status:$orderId:$status";
            }

            $buttons[] = [[
                'text' => $status,
                'callback_data' => $callback
            ]];
        }


        return $buttons;

    }

    private function complete(string $action, mixed $data, $trip)
    {
        if ($trip->courier_type == 'мастер' && $trip->direction == "привоз") {
            $successTemplate = "Модель -
МКАД(ЦКАД) -
Вес -
Парковка -
Соседний сектор -
Монтаж/настройка -
Озвучил (диагност) -
Фактическая проблема (диагност) -
Цена за запчасть (диагност) -
Цена за работу (диагност) -
Сроки (диагност)-
Иное - ";
        } else {
            $successTemplate = "";
        }

        $parts = explode(':', $data);
        if (count($parts) < 3) {
            return;
        }
        $status = $parts[2];

        if ($action == 'success') {
            $template = $successTemplate;
        } elseif ($status == '❌ Отказ') {
            $template = "Причина отказа - ";
        } elseif ($status == '⚠️ Проблемная доставка') {
            $template = "Причина возврата - ";
        } else {
            return;
        }

        $buttons = [[[
            'text' => "Вставить шаблон",
            'switch_inline_query_current_chat' => "/info:{$trip->order_id}:$status\n\n" . $template,
        ]]];
        $replyMarkup = ['inline_keyboard' => $buttons];
        $this->botService->sendMessage($this->chatId, "Для закрытия заказа нажмите на кнопку \"Вставить шаблон\", заполните его и отправьте сообщение.\nПервую строку шаблона (начинается с @fixwill_logistics_bot /info) изменять нельзя!", $replyMarkup);
    }

    private function sendMsg($msg)
    {
        $this->botService->sendMessage($this->chatId, $msg);
    }
}
