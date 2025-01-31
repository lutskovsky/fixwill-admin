<?php

namespace App\Http\Controllers;

use App\Integrations\RemonlineApi;
use App\Models\Scenario;
use App\Models\User;
use App\Services\Telegram\TelegramBotService;
use Illuminate\Http\Request;

class ComagicWebhookController extends Controller
{
    const SITE_ORDER_FIELD = 'f4196099';

    public function handle(Request $request)
    {
        if ($request->action == 'notify') {
            return $this->notify($request);
        } elseif ($request->action == 'create') {
            return $this->create($request);
        }

        return response('Wrong action', 400);
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

        $rem = new RemonlineApi();

        $response = $rem->getOrders(['client_phones' => [$contactPhoneNumber], 'sort_dir' => 'desc']);

        $orders = $response['data'];

        $msgData = [];
        if (empty($orders)) {

            $response = $rem->getClients(['phones' => [$contactPhoneNumber], 'sort_dir' => 'desc']);
            $clients = $response['data'];
            if (empty($clients)) {
                $botService->sendMessage($employee->chat_id, 'Клиент по номеру не найден');
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
            $msg .= "Клиент: <a href='$viewClientUrl'>${client['name']}</a>\n";

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

        $botService->sendMessage($employee->chat_id, $msg);

        return response('OK', 200);
    }

    public function create(Request $request)
    {

        $rem = new RemonlineApi();

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
            return response('Order was not created - excluded scenario', 200);
        }

        $response = $rem->getOrders(['client_phones' => [$number], 'sort_dir' => 'desc']);

        foreach ($response['data'] as $order) {
            // Ищем открытый заказ по тому же сценарию

            // Заказ не считается, если новый или закрыт
            if (in_array($order['status']['group'], [1, 6, 7])) {
                continue;
            }

            // Заказ не считается, если другой сценарий
            $scenarioInOrder = $order['custom_fields'][self::SITE_ORDER_FIELD];
            $scenarioInOrder = trim($scenarioInOrder, " +");
            if ($scenarioInOrder != $scenario) {
                continue;
            }

            // Если мы дошли досюда, значит это открытый заказ по тому же сценарию и новый создавать не надо
            return response('Order was not created - found open order', 200);
        }

        // Если вышли из цикла, значит надо создать новый заказ
        $clients = $rem->getClients(['phones' => [$number]]);

        if ($clients['count'] == 0) {
            $response = $rem->createClient(['name' => "Новый клиент", 'phone' => [$number]]);
            $clientId = $response['data']['id'];
        } else {
            $clientId = $clients['data'][0]['id'];
        }

        /** @var array $orderTypes */
        /** @var array $remonlineCustomFields */
        $customFields = [
            5192512 => 'Москва',
//    1070009 => 'Неизвестно',
//    1070012 => 'Неизвестно',
//    2129012 => 'Неизвестно',
            4196099 => $scenario
        ];

        if (mb_stripos($scenario, 'партнер') !== false) {
            $customFields[4214453] = $scenario;
        }
        $resp = $rem->createOrder([
            'branch_id' => 50230,
            'order_type' => 89790,
            'client_id' => $clientId,

            'custom_fields' => $customFields
        ]);
        return response('Order created', 200);
    }
}
