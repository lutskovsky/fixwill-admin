<?php

namespace App\Http\Controllers;

use App\Integrations\RemonlineApi;
use App\Models\User;
use Illuminate\Http\Request;

class ComagicWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // Retrieve the parameters from the GET request
        $contactPhoneNumber = $request->query('contact_phone_number');
        $employeeNumber = $request->query('employee_number');

        // Validate the required parameters
        if (empty($contactPhoneNumber) || empty($employeeNumber)) {
            return response('Invalid parameters', 400);
        }

        // Search for the employee in the Employees table
        $employee = User::where('internal_phone', $employeeNumber)->first();

        if (!$employee) {
            return response('Employee not found', 404);
        }

        $telegramController = new TelegramController();
        // Create an instance of the RemonlineApi
        $apiToken = env('REMONLINE_TOKEN'); // Ensure you have the API token in your .env file
        $rem = new RemonlineApi($apiToken);

        // Call the getOrders method with the client phone number
        $response = $rem->getOrders(['client_phones' => [$contactPhoneNumber], 'sort_dir' => 'desc']);

        $orders = $response['data'];

        $msgData = [];
        if (empty($orders)) {

            $response = $rem->getClients(['phones' => [$contactPhoneNumber], 'sort_dir' => 'desc']);
            $clients = $response['data'];
            if (empty($clients)) {
                $telegramController->sendMessage($employee->chat_id, 'Клиент по номеру не найден');
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

        $telegramController->sendMessage($employee->chat_id, $msg);

        return response('OK', 200);
    }
}
