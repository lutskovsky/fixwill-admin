<?php

namespace App\Services\Comagic;

use App\Models\Chat;
use App\Models\Message;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ComagicService
{
    private const BASE_URL = 'https://chat-integration-api-prod.uiscom.ru/v1/adapter';
    private const ACCOUNT_ID = 4308;
    private const OPERATOR_ID = 1826499;

    private const CHANNEL_IDS = [
        'whatsapp' => 37010,
        'sms' => 37011, // Update with actual SMS channel ID
    ];

    /**
     * Send a message
     */
    public function sendMessage($phone, $text, $type = 'whatsapp')
    {
        try {
            // Validate type
            if (!in_array($type, ['sms', 'whatsapp'])) {
                throw new Exception('Invalid message type. Must be "sms" or "whatsapp"');
            }

            // Get or create chat
            $chat = $this->getOrCreateChat($phone, $type);

            // Send message
            $messageData = [
                'account_id' => self::ACCOUNT_ID,
                'channel_id' => self::CHANNEL_IDS[$type],
                'chat_id' => $chat->id,
                'source' => 'operator',
                'operator_id' => self::OPERATOR_ID,
                'text' => $text,
                'created_at' => Carbon::now()->toIso8601String(),
            ];

            $response = $this->makeRequest('POST', '/message', $messageData);

            // Save message to database
            $message = Message::create([
                'id' => $response['id'],
                'chat_id' => $chat->id,
                'text' => $response['text'],
                'source' => $response['source'],
                'sent_at' => Carbon::parse($response['created_at']),
            ]);

            return $message;

        } catch (Exception $e) {
            Log::error('Failed to send message: ' . $e->getMessage(), [
                'phone' => $phone,
                'type' => $type,
                'text' => $text,
            ]);
            throw $e;
        }
    }

    /**
     * Get or create chat
     */
    private function getOrCreateChat($phone, $type)
    {
        // Check if chat exists
        $chat = Chat::byPhoneAndType($phone, $type)->first();

        if ($chat) {
            return $chat;
        }

        // Create new chat
        $chatData = [
            'account_id' => self::ACCOUNT_ID,
            'channel_id' => self::CHANNEL_IDS[$type],
            'visitor_phone' => $phone,
            'operator_id' => self::OPERATOR_ID,
            'initiator' => 'operator',
            'created_at' => Carbon::now()->toIso8601String(),
        ];

        $response = $this->makeRequest('POST', '/chat', $chatData);

        // Save chat to database
        $chat = Chat::create([
            'id' => $response['chat_id'],
            'visitor_phone' => $response['visitor_phone'],
            'type' => $type,
        ]);

        return $chat;
    }

    /**
     * Make authenticated request
     */
    private function makeRequest($method, $endpoint, $data = [])
    {
        $token = $this->getAuthToken();

        $request = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response = $method === 'POST'
            ? $request->post(self::BASE_URL . $endpoint, $data)
            : $request->get(self::BASE_URL . $endpoint, $data);

        if (!$response->successful()) {
            // If unauthorized, clear cache and retry once
            if ($response->status() === 401) {
                Cache::forget('comagic_auth_token');
                $token = $this->getAuthToken();

                $request = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                ]);

                $response = $method === 'POST'
                    ? $request->post(self::BASE_URL . $endpoint, $data)
                    : $request->get(self::BASE_URL . $endpoint, $data);
            }

            if (!$response->successful()) {
                throw new Exception('Comagic API error: ' . $response->body());
            }
        }

        return $response->json();
    }

    /**
     * Get authentication token
     */
    private function getAuthToken()
    {
        return Cache::remember('comagic_auth_token', 86400, function () {
            $response = Http::asForm()->post(self::BASE_URL . '/login', [
                'username' => config('services.comagic.username', 'fixwill'),
                'password' => config('services.comagic.password', 'rasa1hague'),
            ]);

            if (!$response->successful()) {
                throw new Exception('Failed to authenticate with Comagic: ' . $response->body());
            }

            $data = $response->json();

            // Cache until expiration (minus 5 minutes for safety)
            $expiresIn = $data['expires_at'] - time() - 300;
            Cache::put('comagic_auth_token', $data['access_token'], $expiresIn);

            return $data['access_token'];
        });
    }

    /**
     * Get chat history
     */
    public function getChatHistory($phone, $type)
    {
        $chat = Chat::byPhoneAndType($phone, $type)->first();

        if (!$chat) {
            return collect();
        }

        return $chat->messages()->orderBy('sent_at', 'asc')->get();
    }

    /**
     * Get all chats for a phone number
     */
    public function getChatsByPhone($phone)
    {
        return Chat::where('visitor_phone', $phone)
            ->with('messages')
            ->get();
    }
}
