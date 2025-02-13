<?php

namespace App\Http\Controllers\TelegramBots;

use App\Http\Controllers\Controller;
use App\Http\Controllers\EmployeeCallController;
use App\Integrations\RemonlineApi;
use App\Models\Courier;
use App\Models\CourierTrip;
use App\Services\Telegram\TelegramBotService;
use Illuminate\Http\Request;

class LogisticsBotController extends Controller
{
    const MANAGERS_CHAT = -1002214584408;
    protected TelegramBotService $botService;
    protected string|null $mode = null;

    public function __construct()
    {
        // Retrieve the token for the logistics bot
        $token = config('telegramBots.logistics');
        // Or: $token = env('TELEGRAM_BOT_TOKEN_LOGISTICS');
        $this->botService = new TelegramBotService($token);
    }

    public function handle(Request $request)
    {
        $data = $request->all();


        if (isset($data['callback_query'])) {
            $this->handleCallback($data);
            return response('OK', 200);
        }

        $message = $data['message']['text'] ?? null;
        $chatId = $data['message']['chat']['id'] ?? null;
        $contact = $data['message']['contact'] ?? null;
        $username = $data['message']['from']['username'] ?? null;

        if ($chatId == self::MANAGERS_CHAT) {
            $this->mode = 'manager';
            $courier = null;
        } else {
            $this->mode = 'courier';

            if ($message === '/start') {
                $this->botService->requestPhoneNumber($chatId);
                return response('OK', 200);
            }

            if ($contact) {
                $phoneNumber = $contact['phone_number'];
                $this->botService->processPhoneNumber($phoneNumber, $chatId, 'Courier');
                return response('OK', 200);
            }

            if ($chatId) {
                $courier = Courier::where('chat_id', $chatId)->first();
                if (!$courier) {
                    $this->botService->sendMessage($chatId, "Не знаю, кто вы, пожалуйста, отправьте /start и поделитесь номером.");
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

            $this->applyChange('set_status', "set_status:$orderId:$status", $trip, $chatId);
//            $this->botService->sendMessage($chatId, $message);
        }


        if ($message === '/active') {
            // ... Show short list of user's trips ...
            $this->listAllTripsShort($courier, $chatId, true);
            return response('OK', 200);
        }

        if ($message === '/history') {
            // ... Show short list of user's trips ...
            $this->listAllTripsShort($courier, $chatId, false);
            return response('OK', 200);
        }

        if ($message && preg_match('/^\/order_(\d+)$/', $message, $matches)) {
            $orderId = $matches[1];
            $this->showTripDetails($chatId, $orderId);
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
    protected function listAllTripsShort(Courier|null $courier, $chatId, $onlyActive)
    {
        if ($courier) {
            if ($onlyActive) {
                $trips = CourierTrip::where('courier_id', $courier->id)
                    ->where('active', true)
                    ->get();
            } else {
                $trips = CourierTrip::where('courier_id', $courier->id)
                    ->where('active', false)
                    ->get();

            }

            $messageText = "Ваши заказы:\n";
            foreach ($trips as $trip) {

                $remonline = new RemonlineApi();

                $order = $remonline->getOrderById($trip->order_id)['data'];
                $address = $order['client']['address'];

                $messageText .= "\nЗаказ {$trip->order_label} ({$trip->direction}) - {$trip->status}\n";
                $messageText .= "$address\n";
                $messageText .= "Подробнее: /order_{$trip->order_id}\n";
            }
        } elseif ($chatId == self::MANAGERS_CHAT) {
            $messageText = "";


            if ($onlyActive) {
                $trips = CourierTrip::where('active', true)->where('moved_on', false)->get();
            } else {
                $trips = CourierTrip::where('active', false)->get();

            }

            $couriers = $trips->groupBy('courier');

            foreach ($couriers as $courier => $trips) {
                $courierText = "- $courier:\n";
                foreach ($trips as $trip) {
                    $courierText .= "<a href='https://web.remonline.app/orders/table/{$trip->order_id}'>{$trip->order_label}</a> ({$trip->direction}) - {$trip->status} /order_{$trip->order_id}\n";
                }

                if (mb_strlen($messageText . $courierText) > 4096) {
                    $this->botService->sendMessage($chatId, $messageText);
                    $messageText = "";
                }
                $messageText .= $courierText . "\n";
            }

        } else {
            return;
        }

        if ($trips->isEmpty()) {
            $this->botService->sendMessage($chatId, "Нет заказов");
            return;
        }
        $this->botService->sendMessage($chatId, $messageText);
    }

    /**
     * Show detailed info about a single trip, plus inline buttons.
     */
    protected function showTripDetails($chatId, $orderId)
    {
        $remonline = new RemonlineApi();

        $order = $remonline->getOrderById($orderId)['data'];

        $trip = CourierTrip::where('order_id', $orderId)->orderBy('id', 'DESC')->first();

        if (!$trip) {
            $this->botService->sendMessage($chatId, "Заказ не найден.");
            return;
        }

        $warning = $trip->courier_id ? '' : " <b>(не пользуется ботом!)</b>";

        $priority = $order['custom_fields']['f1617065'] ?? null;

        $text = "";

        if ($priority) $text .= "!!!ПРИОРИТЕТНЫЙ ЗАКАЗ, ЗАБРАТЬ В ЛЮБОМ СЛУЧАЕ, НЕ ПЕРЕНОСИТЬ!!!\n";

        $text .= "<b>" . mb_strtoupper($trip->direction) . "</b>\n";
        $text .= "Заказ <a href='https://web.remonline.app/orders/table/$orderId'>{$order['id_label']}</a>\n";

        $text .= "Курьер: {$trip->courier}$warning\n";
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

        $text .= "Клиент: {$order['client']['name']}\n";
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
        $this->botService->sendMessage($chatId, $text, $replyMarkup);
    }

    /**
     * Handle callback queries (inline button presses).
     */
    protected function handleCallback(array $update)
    {
        $callbackQuery = $update['callback_query'];
        $data = $callbackQuery['data'] ?? null;
        $chatId = $callbackQuery['message']['chat']['id'];
        $messageId = $callbackQuery['message']['message_id'];

        if (!$data) {
            return;
        }

        [$action, $orderId] = explode(':', $data);
        $trip = CourierTrip::where('order_id', $orderId)->orderBy('id', 'DESC')->first();

        if ($action == 'call') {

            $this->call($data, $chatId);
            return;
        }

        if (!$trip) {
            // You might want to answerCallbackQuery here
            $this->botService->sendMessage($chatId, "Заказ не найден :(");
            return;
        }

        switch ($action) {
            case 'change_status':
                $this->showStatusOptions($chatId, $orderId, $trip->direction);
                break;

            case 'change_arrival':
                $this->showArrivalOptions($chatId, $orderId);
                break;

            case 'set_status':
            case 'set_arrival':
            $this->applyChange($action, $data, $trip, $chatId);
                break;
            case 'success':
            case 'fail':
                $this->complete($action, $data, $trip, $chatId, $messageId);

        }
    }

    protected function call($data, $chatId)
    {
        $parts = explode(':', $data);
        if (count($parts) < 2) {
            return;
        }

        $phone = $parts[1];
        EmployeeCallController::courierCall($phone, $chatId);
    }

    protected function showStatusOptions($chatId, $orderId, $direction)
    {
        $statuses = [
            'привоз' => ['Назначен', 'Забрал', 'Взял >1000, не забрал', 'Отказ'],
            'отвоз' => ['Назначен', 'Отдал товар', 'Проблемная доставка'],
        ];
        $buttons = [];
        foreach ($statuses[$direction] as $status) {
            $buttons[] = [[
                'text' => ucfirst($status),
                'callback_data' => "set_status:$orderId:$status"
            ]];
        }

        $replyMarkup = ['inline_keyboard' => $buttons];
        $this->botService->sendMessage($chatId, 'Выберите Этап:', $replyMarkup);
    }

    protected function showArrivalOptions($chatId, $orderId)
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
        $this->botService->sendMessage($chatId, 'Выберите интервал:', $replyMarkup);
    }

    /**
     * Apply the chosen status or arrival interval to the trip.
     */
    protected function applyChange($action, $data, CourierTrip $trip, $chatId)
    {
        // data is like "set_status:delivered:123" or "set_arrival:9-12:123"
        $parts = explode(':', $data);
        if (count($parts) < 3) {
            return;
        }

        $value = $parts[2]; // e.g. "delivered" or "9-12"

        if ($action === 'set_status') {
            $trip->update(['status' => $value]);
            $msg = "Этап заказа <a href='https://web.remonline.app/orders/table/{$trip->order_id}'>{$trip->order_label}</a> изменён на $value.";

        } elseif ($action === 'set_arrival') {
            $trip->update(['arrival_time' => $value]);
            $msg = "Время заказа <a href='https://web.remonline.app/orders/table/{$trip->order_id}'>{$trip->order_label}</a> изменено на $value.";
        }


        if (isset($msg)) {
            $msg .= "\n/order_{$trip->order_id}";
            $this->botService->sendMessage($chatId, $msg);
            if ($chatId !== self::MANAGERS_CHAT) {
                $this->botService->sendMessage(self::MANAGERS_CHAT, $msg);
            }
        }
    }

    protected function getInlineKeyboard($trip)
    {
        $orderId = $trip->order_id;
        $buttons = [];

        if (!$trip->active) {
            return $buttons;
        }

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
                'Забрал' => "success:$orderId:Забрал"];

            if ($trip->courier_type == 'мастер') {
                $options['Взял >1000, не забрал'] = null;
            }
            $options['Перенести время'] = "change_arrival:$orderId";
            $options['Отказ'] = "fail:$orderId:Отказ";


        } else {
            $options = [
                'Отдал товар' => "success:$orderId:Отдал товар",
//                'Перенести время' => "change_arrival:$orderId",
                'Проблемная доставка' => "fail:$orderId:Проблемная доставка"
            ];
        }

        foreach ($options as $status => $callback) {
            if (!$callback) {
                $callback = "set_status:$orderId:$status";
            }

            $buttons[] = [[
                'text' => ucfirst($status),
                'callback_data' => $callback
            ]];
        }


        return $buttons;

    }

    private function complete(string $action, mixed $data, $trip, mixed $chatId, mixed $messageId)
    {
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

        $parts = explode(':', $data);
        if (count($parts) < 3) {
            return;
        }
        $status = $parts[2];

        if ($action == 'success') {
            $template = $successTemplate;
        } elseif ($status == 'Отказ') {
            $template = "Причина отказа - ";
        } elseif ($status == 'Проблемная доставка') {
            $template = "Причина возврата - ";
        } else {
            return;
        }

        $buttons = [[[
            'text' => "Вставить шаблон",
            'switch_inline_query_current_chat' => "/info:{$trip->order_id}:$status\n\n" . $template,
        ]]];
        $replyMarkup = ['inline_keyboard' => $buttons];
        $this->botService->sendMessage($chatId, 'Для закрытия заказа нажмите на кнопку "Вставить шаблон", заполните его и отправьте сообщение.', $replyMarkup);

    }
}
