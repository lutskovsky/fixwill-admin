<?php

namespace App\Http\Controllers\TelegramBots;

use App\Http\Controllers\Controller;
use App\Services\Telegram\TelegramBotService;
use Illuminate\Http\Request;

class CallNotificationsBotController extends Controller
{
    protected TelegramBotService $botService;

    public function __construct()
    {
        // Retrieve the token for the call_notifications bot from config or env
        $token = config('telegramBots.call_notifications');
        // Or: $token = env('TELEGRAM_BOT_TOKEN_CALL_NOTIFICATIONS');
        $this->botService = new TelegramBotService($token);
    }

    public function handle(Request $request)
    {
        $data = $request->all();

        // Check if a message is present
        if (isset($data['message'])) {
            $messageText = $data['message']['text'] ?? null;
            $chatId = $data['message']['chat']['id'];
            $contact = $data['message']['contact'] ?? null;

            // If it's the /start command
            if ($messageText === '/start') {
                $this->botService->requestPhoneNumber($chatId);
                return response('OK', 200);
            }

            // If the user shared their contact
            if ($contact) {
                $phoneNumber = $contact['phone_number'];
                $this->botService->processPhoneNumber($phoneNumber, $chatId);
                return response('OK', 200);
            }
        }

        return response('OK', 200);
    }
}
