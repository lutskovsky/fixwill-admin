<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use Illuminate\Support\Facades\Log;

class TelegramController extends Controller
{
    public function handle(Request $request)
    {
        Log::channel('telegram')->info('Telegram Webhook Request:', $request->all());
        $data = $request->all();

        if (isset($data['message']['text'])) {
            $message = $data['message']['text'];
            $chatId = $data['message']['chat']['id'];

            if ($message === '/start') {
                return $this->requestPhoneNumber($chatId);
            }
        } elseif (isset($data['message']['contact'])) {
            $phoneNumber = $data['message']['contact']['phone_number'];
            return $this->processPhoneNumber($phoneNumber, $data['message']['chat']['id']);
        }

        return response('OK', 200);
    }

    protected function requestPhoneNumber($chatId)
    {
        $replyMarkup = [
            'one_time_keyboard' => true,
            'keyboard' => [
                [['text' => 'Поделиться номером', 'request_contact' => true]],
            ],
            'resize_keyboard' => true,
        ];

        $this->sendMessage($chatId, 'Пожалуйста, нажмите на кнопку "Поделиться номером".', $replyMarkup);

        return response('OK', 200);
    }

    protected function processPhoneNumber($phoneNumber, $chatId)
    {
        $phoneNumber = preg_replace('/\D/', '', $phoneNumber);
        $employee = Employee::where('tg_login', $phoneNumber)->first();

        if ($employee) {
            $employee->chat_id = $chatId;
            $employee->save();

            $this->sendMessage($chatId, 'Спасибо, вы зарегистрированы.');
        } else {
            $this->sendMessage($chatId, 'Ошибка: сотрудника с таким номером нет в настройках.');
        }

        return response('OK', 200);
    }

    public function sendMessage($chatId, $text, $replyMarkup = null)
    {
        $url = "https://api.telegram.org/bot" . env('TELEGRAM_BOT_TOKEN') . "/sendMessage";

        $data = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' =>'html'
        ];

        if ($replyMarkup) {
            $data['reply_markup'] = json_encode($replyMarkup);
        }

        $response = \Http::post($url, $data);


        if (!$response->successful()) {
            Log::error('Failed to send message to chat: ' . $response->body());
        }

        return $response->successful();
    }
}
