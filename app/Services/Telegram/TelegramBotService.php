<?php

namespace App\Services\Telegram;

use Illuminate\Support\Facades\Http;

class TelegramBotService
{
    protected string $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    /**
     * Request the user to share their phone number via Telegram's 'request_contact'.
     */
    public function requestPhoneNumber(int|string $chatId, string $prompt = 'Пожалуйста, нажмите на кнопку "Поделиться номером'): bool
    {
        $replyMarkup = [
            'one_time_keyboard' => true,
            'keyboard' => [
                [[
                    'text' => 'Поделиться номером',
                    'request_contact' => true
                ]],
            ],
            'resize_keyboard' => true,
        ];

        return $this->sendMessage($chatId, $prompt, $replyMarkup);
    }

    /**
     * Send a text message to the specified chat.
     */
    public function sendMessage(int|string $chatId, string $text, array $replyMarkup = null)
    {
        $url = 'https://api.telegram.org/bot' . $this->token . '/sendMessage';

        $data = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ];

        if ($replyMarkup) {
            $data['reply_markup'] = $replyMarkup;
        }

        $response = Http::post($url, $data);

        return $response;
    }

    /**
     * Process phone number: register the user’s Telegram chat_id by matching phone number to tg_login.
     */
    public function processPhoneNumber(string $phoneNumber, int|string $chatId, string $model = 'User'): bool
    {
        // Strip non-digits from the phone number
        $normalizedPhone = preg_replace('/\D/', '', $phoneNumber);

        $model = "App\Models\\" . $model;

        // Attempt to find a user with matching tg_login
        $user = $model::where('tg_login', $normalizedPhone)->first();

        if ($user) {
            $user->chat_id = $chatId;
            $user->save();

            $this->sendMessage($chatId, 'Спасибо, вы зарегистрированы.');
            return true;
        }

        $this->sendMessage($chatId, 'Ошибка: сотрудника с таким номером нет в настройках.');
        return false;
    }
}
