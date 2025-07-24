<?php

namespace App\Http\Controllers;

use App\Integrations\RemonlineApi;
use App\Listeners\TransferIssueNotification;
use App\Models\Channel;
use App\Models\Chat;
use App\Models\Message;
use App\Models\Scenario;
use App\Models\Status;
use App\Models\TransferIssue;
use App\Models\User;
use App\Services\Telegram\TelegramBotService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ComagicWebhookController extends Controller
{
    const SITE_ORDER_FIELD = 'f4196099';

    private RemonlineApi $remonline;

    public function __construct(RemonlineApi $remonline)
    {
        $this->remonline = $remonline;
    }

    /**
     * Main handler that routes to specific webhook handlers
     */
    public function handle(Request $request)
    {
        // Handle call webhooks (existing functionality)
        if ($request->action == 'notify') {
            return $this->notify($request);
        } elseif ($request->action == 'create') {
            return $this->create($request);
        } elseif ($request->action == 'outgoing') {
            return $this->outgoingCall($request);
        }

        return response('Wrong action', 400);
    }

    /**
     * Handle incoming chat message webhook
     */
    public function handleChatMessage(Request $request)
    {
        try {
            // Log the incoming webhook for debugging
            Log::channel('comagic_chat')->info('Incoming chat message webhook:', $request->all());

            // Extract data from webhook
            $data = $request->all();

            // Validate required fields
            if (!isset($data['id']) || !isset($data['chat_id']) || !isset($data['text'])) {
                Log::channel('comagic_chat')->error('Missing required fields in webhook');
                return response()->json(['error' => 'Missing required fields'], 400);
            }

            // Find or create chat
            $chat = Chat::find($data['chat_id']);

            if (!$chat) {
                return response()->json(['status' => 'ok']);
            }

            // Check if message already exists (to prevent duplicates)
            $existingMessage = Message::find($data['id']);
            if ($existingMessage) {
                Log::channel('comagic_chat')->info('Message already exists, skipping', ['message_id' => $data['id']]);
                return response()->json(['status' => 'ok']);
            }

            // Create new message
            $message = Message::create([
                'id' => $data['id'],
                'chat_id' => $chat->id,
                'text' => $data['text'],
                'source' => $data['source'],
                'sent_at' => Carbon::parse($data['created_at']),
            ]);

            Log::channel('comagic_chat')->info('Message saved successfully', [
                'message_id' => $message->id,
                'chat_id' => $chat->id,
                'source' => $message->source
            ]);

            if ($data['source'] === 'visitor') {
                $this->notifyOperatorsAboutMessage($chat, $message);
            }

            return response()->json(['status' => 'ok']);

        } catch (Exception $e) {
            Log::channel('comagic_chat')->error('Error processing chat webhook: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            // Return success anyway to prevent webhook retries
            return response()->json(['status' => 'ok']);
        }
    }


    public function notify(Request $request)
    {
        $contactPhoneNumber = $request->query('contact_phone_number');
        $employeeNumber = $request->query('employee_number');

        if (empty($contactPhoneNumber) || empty($employeeNumber)) {
            return response('Invalid parameters', 400);
        }

        $employee = User::where('internal_phone', $employeeNumber)->first();

        if (!$employee) {
            return response('Employee not found', 404);
        }

        $token = config('telegramBots.call_notifications');
        $botService = new TelegramBotService($token);


        $response = $this->remonline->getOrders(['client_phones' => [$contactPhoneNumber], 'sort_dir' => 'desc']);

        $orders = $response['data'];

        $msgData = [];
        if (empty($orders)) {

            $response = $this->remonline->getClients(['phones' => [$contactPhoneNumber], 'sort_dir' => 'desc']);
            $clients = $response['data'];
            if (empty($clients)) {
                if ($employee->chat_id) {
                    $botService->sendMessage($employee->chat_id, 'Клиент по номеру не найден');
                }
                return response('No clients', 200);
            }
            foreach ($clients as $client) {
                $clientId = $client['id'];
                $msgData[$clientId]['name'] = $client['name'];
                $msgData[$clientId]['orders'] = [];
            }
        }

        foreach ($orders as $order) {
            $clientId = $order['client']['id'];
            if (!isset($msgData[$clientId])) {
                $msgData[$clientId]['name'] = $order['client']['name'];
                $msgData[$clientId]['orders'] = [];
            }
            $msgData[$clientId]['orders'][] = $order;
        }

        $msg = "";
        foreach ($msgData as $clientId => $client) {
            $viewClientUrl = route('client.show', $clientId);
            $msg .= "Клиент: <a href='$viewClientUrl'>{$client['name']}</a>\n";

            if (empty($client['orders'])) {
                $msg .= "Заказов нет\n";
            } else {
                foreach ($client['orders'] as $order) {
                    $id = $order['id'];
                    $label = $order['id_label'];
                    $status = $order['status']['name'];
                    $msg .= "<a href='https://web.remonline.app/orders/table/$id'>$label</a> - $status\n";
                }
            }

            $newOrderUrl = route('client.order.create', $clientId);

            $msg .= "<a href='$newOrderUrl'>Создать новый заказ</a>\n\n";
        }

        if ($employee->chat_id) {
            $botService->sendMessage($employee->chat_id, $msg);
        }

        if ($employee->group_id == 1) {
            $botService->sendMessage("-4942029113", $msg . "Ответил " . $employee->name);
        }

        return response('OK', 200);
    }

    /**
     * Existing create method for order creation
     */
    public function create(Request $request)
    {
        Log::channel('create-order')->info($request->query());

        $number = $request->query('contact_phone_number');
        if (!$number) {
            return response('Phone number missing', 400);
        }

        if ($number == '74950218573') die();

        if ($request->query('scenario')) {
            $scenario = trim($request->query('scenario'), " +");
        } else $scenario = '';

        $dbScenario = Scenario::where('name', $scenario)->first();

        if ($dbScenario && $dbScenario->skip_order_creation) {
            Log::channel('create-order')->info('Order was not created - excluded scenario');
            return response('Order was not created - excluded scenario', 200);
        }

        $response = $this->remonline->getOrders(['client_phones' => [$number], 'sort_dir' => 'desc']);

        foreach ($response['data'] as $order) {
            // Ищем текущий заказ по тому же сценарию

            // Заказ не считается, если не текущий
            $statusId = $order['status']['id'];
            $statusSettings = Status::where('status_id', $statusId)->first();

            // Если статус ещё не синхронизирован, смотрим на группу
            if (!$statusSettings) {
                if (in_array($order['status']['group'], [1, 6, 7])) {
                    continue;
                }
            } elseif (!$statusSettings->current) {
                continue;
            }

            // Заказ не считается, если другой сценарий
            $scenarioInOrder = $order['custom_fields'][self::SITE_ORDER_FIELD] ?? '';
            $scenarioInOrder = trim($scenarioInOrder, " +");
            if ($scenarioInOrder != $scenario) {
                continue;
            }

            Log::channel('create-order')->info('Order was not created - found open order');

            // Если мы дошли досюда, значит это открытый заказ по тому же сценарию и новый создавать не надо
            return response('Order was not created - found open order', 200);
        }

        // Если вышли из цикла, значит надо создать новый заказ
        $clients = $this->remonline->getClients(['phones' => [$number]]);

        if ($clients['count'] == 0) {
            $response = $this->remonline->createClient(['name' => "Новый клиент", 'phone' => [$number]]);
            $clientId = $response['data']['id'];

            Log::channel('create-order')->info("Created client $clientId");
        } else {
            $clientId = $clients['data'][0]['id'];
            Log::channel('create-order')->info("Found client $clientId");
        }

        /** @var array $orderTypes */
        $customFields = [
            5192512 => 'Москва',
            4196099 => $scenario
        ];

        if (mb_stripos($scenario, 'партнер') !== false) {
            $customFields[4214453] = $scenario;
        }
        $resp = $this->remonline->createOrder([
            'branch_id' => 50230,
            'order_type' => 89790,
            'client_id' => $clientId,
            'custom_fields' => $customFields
        ]);

        $orderId = $resp['data']['id'];

        Log::channel('create-order')->info("Created order $orderId");
        return response('Order created', 200);
    }

    /**
     * Report courier call error
     */
    public function reportCourierCallError(Request $request)
    {
        $sessionId = $request->query("call_session_id");
        if ($chatId = Cache::pull('call_session_' . $sessionId)) {
            $botService = new TelegramBotService(config('telegramBots.logistics'));
            $botService->sendMessage($chatId, "Не получилось совершить звонок: " . $request->query("lost_reason"));
            $botService = new TelegramBotService(config('telegramBots.notifications'));
            $botService->sendMessage("-4687255586", $request->query("text"));
        }
    }

    /**
     * Handle outgoing call webhook
     */
    public function outgoingCall(Request $request)
    {
        $issues = TransferIssue::whereJsonContains('phones', $request->query('number'))->get();

        if ($issues) {
            $notifier = new TransferIssueNotification();
            foreach ($issues as $issue) {
                $issue->called = true;
                $issue->save();
                $notifier->updateMessage($issue);
            }
        }
        return response('OK', 200);
    }

    /**
     * Notify operators about new message
     */
    private function notifyOperatorsAboutMessage($chat, $message)
    {
        $channelId = $chat->channel_id;
        $channel = Channel::where('comagic_id', $channelId)->first();
        $group = $channel->whatsappGroups()->first();
        $tgChatId = $group->tg_chat_id;

        if (!$tgChatId) {
            return;
        }

        $clientId = $chat->client_id;

        $clientData = $this->remonline->getClientById($clientId);

        $viewClientUrl = route('client.show', $clientId);
        $msg = "Клиент: <a href='$viewClientUrl'>{$clientData['name']}</a>\n\n";

        $orders = $this->remonline->getOrders(['clients_ids' => [$clientId], 'sort_dir' => 'desc'])['data'];
        foreach ($orders as $order) {
            $msg .= "<a href='https://web.remonline.app/orders/table/{$order['id']}'>{$order['id_label']}</a> {$order['status']['name']}\n";

        }

        $msg .= "\n";
        $msg .= $message->text;

        $token = config('telegramBots.notifications');
        $botService = new TelegramBotService($token);

        $botService->sendMessage($tgChatId, $msg);
    }
}
