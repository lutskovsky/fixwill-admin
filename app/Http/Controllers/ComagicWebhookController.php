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

        // Create an instance of the RemonlineApi
        $apiToken = env('REMONLINE_TOKEN'); // Ensure you have the API token in your .env file
        $rem = new RemonlineApi($apiToken);

        // Call the getOrders method with the client phone number
        $response = $rem->getOrders(['client_phones' => [$contactPhoneNumber]]);

        $data = $response['data'];

        if (empty($data)) return response('No orders', 200);

        $msg = "Звонок по заказам:\n\n";

        foreach ($data as $order) {

            $id = $order['id'];
            $label = $order['id_label'];
            $status = $order['status']['name'];
            $msg .= "<a href='https://web.remonline.app/orders/table/$id'>$label</a> - $status\n";
        }
        $telegramController = new TelegramController();
        $telegramController->sendMessage($employee->chat_id, $msg);

        return response('OK', 200);
    }
}
