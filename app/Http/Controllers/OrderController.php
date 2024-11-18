<?php

namespace App\Http\Controllers;

use App\Integrations\RemonlineApi;
use Illuminate\Support\Facades\Crypt;

class OrderController extends Controller
{
    public function getClient($orderLabel)
    {
        $remonline = new RemonlineApi(env('REMONLINE_TOKEN'));
        $customFieldsResp = $remonline->getClientCustomFields();
        $customFields = [];
        foreach ($customFieldsResp['data'] as $field) {
            $customFields[$field['id']] = $field['name'];
        }

        $order = $remonline->getOrders(['id_labels' => [$orderLabel]]);
        $client = $order['data'][0]['client'];
        $clientJson = [
            'name' => $client['name'],
            'email' => $client['email'],

        ];
        foreach ($client['custom_fields'] as $id => $value) {
            if (is_bool($value)) {
                $value = $value ? "Да" : "Нет";
            }

            $id = substr($id, 1);
            $clientJson[$customFields[$id]] = $value;
        }

        $clientJson['Примечание'] = $client['notes'];

        if (isset($client['phone'])) {
            $phones = [];
            foreach ($client['phone'] as $phone) {
                $phones[] = [
                    'text' => substr($phone, 0, 7) . '****',
                    'encrypted' => Crypt::encryptString($phone)
                ];
            }
            $clientJson['phones'] = $phones;
        }

        return response()->json($clientJson);
    }
}
