<?php

namespace App\Http\Controllers\TelegramBots;

use App\Http\Controllers\Controller;
use App\Http\Controllers\EmployeeCallController;
use App\Integrations\RemonlineApi;
use App\Models\CourierTrip;
use App\Models\User;
use App\Services\Telegram\TelegramBotService;
use Illuminate\Http\Request;

class LogisticsBotController extends Controller
{
    const MANAGERS_CHAT = -4616424463;
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
        $message = $data['message']['text'] ?? null;
        $chatId = $data['message']['chat']['id'] ?? null;
        $contact = $data['message']['contact'] ?? null;
        $username = $data['message']['from']['username'] ?? null;

        if ($chatId == self::MANAGERS_CHAT) {
            $this->mode = 'manager';
            $user = null;
        } else {
            $this->mode = 'courier';

            if ($message === '/start') {
                $this->botService->requestPhoneNumber($chatId);
                return response('OK', 200);
            }

            if ($contact) {
                $phoneNumber = $contact['phone_number'];
                $this->botService->processPhoneNumber($phoneNumber, $chatId);
                return response('OK', 200);
            }

            $user = User::where('chat_id', $chatId)->first();
            if (!$user) {
                $this->botService->sendMessage($chatId, "Не знаю, кто вы, пожалуйста, отправьте /start и поделитесь номером.");
                return response('OK', 200);
            }
        }


        if ($message === '/all') {
            // ... Show short list of user's trips ...
            $this->listAllTripsShort($user, $chatId);
            return response('OK', 200);
        }

        if ($message && preg_match('/^(.+)@fixwill_logistics_bot$/', $message, $matches)) {
            $message = $matches[1];
        }

        if ($message && preg_match('/^\/order_(\d+)$/', $message, $matches)) {
            $orderId = $matches[1];
            $this->showTripDetails($chatId, $orderId);
            return response('OK', 200);
        }

        // 4) If this is a callback query for inline buttons
        if (isset($data['callback_query'])) {
            $this->handleCallback($data);
            return response('OK', 200);
        }

        if ($this->mode == 'courier') {
            $messageToManagers = "Сообщение от {$user->remonline_courier}";
            if ($username)
                $messageToManagers .= " (@$username)";

            $messageToManagers .= ": \n" . $message;

            $this->botService->sendMessage(self::MANAGERS_CHAT, $messageToManagers);
        }
        return response('OK', 200);
    }

    /**
     * Show a short list of the user’s trips in one message.
     */
    protected function listAllTripsShort(User|null $user, $chatId)
    {
        if ($user) {
            $trips = CourierTrip::where('user_id', $user->id)->get();
        } elseif ($chatId == self::MANAGERS_CHAT) {
            $trips = CourierTrip::all();
        }


        if ($trips->isEmpty()) {
            $this->botService->sendMessage($chatId, "Нет заказов");
            return;
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

        $this->botService->sendMessage($chatId, $messageText);
    }

    /**
     * Show detailed info about a single trip, plus inline buttons.
     */
    protected function showTripDetails($chatId, $orderId)
    {
        $remonline = new RemonlineApi();

        $order = $remonline->getOrderById($orderId)['data'];

        $trip = CourierTrip::where('order_id', $orderId)->first();

        if (!$trip) {
            $this->botService->sendMessage($chatId, "Order not found.");
            return;
        }

        $text = "Заказ {$order['id_label']}\n";
        $text .= "Курьер: {$trip->courier}\n";
        $text .= "Направление: {$trip->direction}\n";
        $text .= "Статус: {$trip->status}\n";
        $text .= "Время: {$trip->arrival_time}\n";
        $text .= "Клиент: {$order['client']['name']}\n";
        $text .= "Адрес: {$order['client']['address']}\n";
        $text .= "Подъезд: {$order['client']['custom_fields']['f3532128']}\n";
        $text .= "Этаж: {$order['client']['custom_fields']['f3532129']}\n";
        $text .= "Квартира: {$order['client']['custom_fields']['f3532130']}\n";
        $text .= "Метро: {$order['client']['custom_fields']['f3452769']}\n";
        $text .= "Оборудование: {$order['custom_fields']['f1070009']} {$order['custom_fields']['f1070012']}\n";


        $phones = array_map(
            fn($phone) => [[
                'text' => '📞 ' . substr($phone, 0, 7) . '****',
                'callback_data' => "call:" . $phone
//                'callback_data' => "call:" . Crypt::encryptString($phone)
            ]],
            $order['client']['phone']);


//        $this->botService->sendMessage($chatId, print_r($phones,true));


        $inlineKeyboard = [
            [
                ['text' => 'Статус', 'callback_data' => "change_status:{$trip->order_id}"],
                ['text' => 'Время', 'callback_data' => "change_arrival:{$trip->order_id}"],
            ]
        ];

        $inlineKeyboard = array_merge($inlineKeyboard, $phones);

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
//        $user = User::where('chat_id', $chatId)->first();
        $trip = CourierTrip::where('order_id', $orderId)->first();

        if ($action == 'call') {

            $this->call($data, $chatId);
            return;
        }

        if (!$trip) {
            // You might want to answerCallbackQuery here
            $this->botService->sendMessage($chatId, "Trip not found.");
            return;
        }

        switch ($action) {
            case 'change_status':
                $this->showStatusOptions($chatId, $orderId);
                break;

            case 'change_arrival':
                $this->showArrivalOptions($chatId, $orderId);
                break;

            case 'set_status':
            case 'set_arrival':
                $this->applyChange($action, $data, $trip, $chatId, $messageId);
                break;
        }
    }

    protected function call($data, $chatId)
    {
        $parts = explode(':', $data);
        if (count($parts) < 2) {
            return;
        }

        $phone = $parts[1];
        EmployeeCallController::call($phone, $chatId);
    }

    protected function showStatusOptions($chatId, $orderId)
    {
        $statuses = ['Назначен', 'В работе', 'Отказ', 'Выполнен'];
        $buttons = [];
        foreach ($statuses as $status) {
            $buttons[] = [[
                'text' => ucfirst($status),
                'callback_data' => "set_status:$orderId:$status"
            ]];
        }

        $replyMarkup = ['inline_keyboard' => $buttons];
        $this->botService->sendMessage($chatId, 'Выберите статус:', $replyMarkup);
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
    protected function applyChange($action, $data, CourierTrip $trip, $chatId, $messageId)
    {
        // data is like "set_status:delivered:123" or "set_arrival:9-12:123"
        $parts = explode(':', $data);
        if (count($parts) < 3) {
            return;
        }

        $value = $parts[2]; // e.g. "delivered" or "9-12"

        if ($action === 'set_status') {
            $trip->update(['status' => $value]);
            $msg = "Статус заказа {$trip->order_label} изменён на $value.";
        } elseif ($action === 'set_arrival') {
            $trip->update(['arrival_time' => $value]);
            $msg = "Время заказа {$trip->order_label} изменено на $value.";
        }

        if (isset($msg)) {
            $msg .= "\n/order_{$trip->order_id}";
            $this->botService->sendMessage($chatId, $msg);
            if ($chatId !== self::MANAGERS_CHAT) {
                $this->botService->sendMessage(self::MANAGERS_CHAT, $msg);
            }
        }
    }

    /**
     * Convert an interval like "9-12" into a representative time (e.g., "09:00:00").
     */
    protected function intervalToTime($interval)
    {
        [$start, $end] = explode('-', $interval);
        return str_pad($start, 2, '0', STR_PAD_LEFT) . ":00:00";
    }
}
