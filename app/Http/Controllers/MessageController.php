<?php

namespace App\Http\Controllers;

use App\Services\Comagic\ComagicChatService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class MessageController extends Controller
{
    private $comagicService;

    public function __construct(ComagicChatService $comagicService)
    {
        $this->comagicService = $comagicService;
    }

    /**
     * Send a message
     */
    public function send(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'required|string',
            'text' => 'required|string',
            'type' => 'required|in:sms,whatsapp',
            'encryptedPhone' => 'nullable|string',
        ]);

        // Decrypt phone if encrypted
        $phone = $this->decryptPhone($request->phone, $request->encryptedPhone);

        try {
            $message = $this->comagicService->sendMessage(
                $phone,
                $request->text,
                $request->clientId,
                $request->type,
            );

            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully',
                'data' => $message,
            ]);
        } catch (Exception $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Failed to send message',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Decrypt phone number if encrypted
     */
    private function decryptPhone($phoneText, $encryptedPhone = null)
    {
        if (str_contains($phoneText, '*') && $encryptedPhone) {
            return Crypt::decryptString($encryptedPhone);
        }

        // Clean phone number
        return preg_replace('/\D/', '', $phoneText);
    }

    /**
     * Get chat history
     */
    public function history(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'required|string',
            'type' => 'required|in:sms,whatsapp',
        ]);
        $phone = $this->decryptPhone($request->phone, $request->encryptedPhone);

        try {
            $messages = $this->comagicService->getChatHistory(
                $phone,
                $request->type
            );

            return response()->json([
                'success' => true,
                'data' => $messages,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve chat history',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all chats for a phone number
     */
    public function chats($phone): JsonResponse
    {
        try {
            $chats = $this->comagicService->getChatsByPhone($phone);

            return response()->json([
                'success' => true,
                'data' => $chats,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve chats',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
