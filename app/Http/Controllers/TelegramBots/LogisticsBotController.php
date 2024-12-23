<?php

namespace App\Http\Controllers\TelegramBots;

use App\Http\Controllers\Controller;
use App\Models\CourierTrip;
use App\Models\User;
use App\Services\Telegram\TelegramBotService;
use Illuminate\Http\Request;

class LogisticsBotController extends Controller
{
    protected TelegramBotService $botService;

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

        // 1) If user just typed /start or shared contact
        if ($message === '/start') {
            $this->botService->requestPhoneNumber($chatId, 'Please click on the button to share your phone.');
            return response('OK', 200);
        }

        if ($contact) {
            $phoneNumber = $contact['phone_number'];
            $this->botService->processPhoneNumber($phoneNumber, $chatId);
            return response('OK', 200);
        }

        // 2) Identify if user is registered
        $user = User::where('chat_id', $chatId)->first();
        if (!$user) {
            // Not recognized
            $this->botService->sendMessage($chatId, "I don't recognize you. Please /start and share your phone.");
            return response('OK', 200);
        }

        // 3) Handle the logistics-specific commands
        if ($message === '/all') {
            // ... Show short list of user's trips ...
            $this->listAllTripsShort($user, $chatId);
            return response('OK', 200);
        }

        if ($message && preg_match('/^\/order_(\d+)$/', $message, $matches)) {
            $orderId = $matches[1];
            $this->showTripDetails($user, $chatId, $orderId);
            return response('OK', 200);
        }

        // 4) If this is a callback query for inline buttons
        if (isset($data['callback_query'])) {
            $this->handleCallback($data);
            return response('OK', 200);
        }

        // If none of the above
        $this->botService->sendMessage($chatId, "Commands:\n/all - show your trips\n/order_{id} - show trip details");
        return response('OK', 200);
    }

    /**
     * Show a short list of the user’s trips in one message.
     */
    protected function listAllTripsShort(User $user, $chatId)
    {
        $trips = CourierTrip::where('user_id', $user->id)->get();

        if ($trips->isEmpty()) {
            $this->botService->sendMessage($chatId, "You have no active trips.");
            return;
        }

        $messageText = "Your trips:\n";
        foreach ($trips as $trip) {
            $messageText .= "\nOrder ID: {$trip->order_id} ({$trip->direction}) - {$trip->status}\n";
            $messageText .= "Details: /order_{$trip->order_id}\n";
        }

        $this->botService->sendMessage($chatId, $messageText);
    }

    /**
     * Show detailed info about a single trip, plus inline buttons.
     */
    protected function showTripDetails(User $user, $chatId, $orderId)
    {
        $trip = CourierTrip::where('user_id', $user->id)->where('order_id', $orderId)->first();

        if (!$trip) {
            $this->botService->sendMessage($chatId, "Order not found.");
            return;
        }

        $text = "Order ID: {$trip->order_id}\n";
        $text .= "Direction: {$trip->direction}\n";
        $text .= "Status: {$trip->status}\n";
        $text .= "Arrival Time: {$trip->arrival_time}\n";

        $inlineKeyboard = [
            [
                ['text' => 'Change Status', 'callback_data' => "change_status:{$trip->order_id}"],
                ['text' => 'Change Arrival', 'callback_data' => "change_arrival:{$trip->order_id}"],
            ]
        ];

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
        $user = User::where('chat_id', $chatId)->first();
        $trip = CourierTrip::where('user_id', $user->id)->where('order_id', $orderId)->first();

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

    protected function showStatusOptions($chatId, $orderId)
    {
        $statuses = ['pending', 'in-transit', 'delivered', 'canceled'];
        $buttons = [];
        foreach ($statuses as $status) {
            $buttons[] = [[
                'text' => ucfirst($status),
                'callback_data' => "set_status:$status:$orderId"
            ]];
        }

        $replyMarkup = ['inline_keyboard' => $buttons];
        $this->botService->sendMessage($chatId, 'Choose a new status:', $replyMarkup);
    }

    protected function showArrivalOptions($chatId, $orderId)
    {
        $arrivalIntervals = ['9-12', '12-15', '15-18'];
        $buttons = [];
        foreach ($arrivalIntervals as $interval) {
            $buttons[] = [[
                'text' => $interval,
                'callback_data' => "set_arrival:$interval:$orderId"
            ]];
        }

        $replyMarkup = ['inline_keyboard' => $buttons];
        $this->botService->sendMessage($chatId, 'Choose an arrival interval:', $replyMarkup);
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

        $value = $parts[1]; // e.g. "delivered" or "9-12"

        if ($action === 'set_status') {
            $trip->update(['status' => $value]);
            $this->botService->sendMessage($chatId, "Status updated to $value.");
        } elseif ($action === 'set_arrival') {
            $trip->update(['arrival_time' => now()->format('Y-m-d') . ' ' . $this->intervalToTime($value)]);
            $this->botService->sendMessage($chatId, "Arrival time updated to $value.");
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
