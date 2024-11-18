<?php

namespace App\Http\Controllers;

use App\Integrations\RemonlineApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Inertia\Inertia;

class OrderClientController extends Controller
{
    protected RemonlineApi $remonline;

    public function __construct(RemonlineApi $remonline)
    {
        $this->remonline = $remonline;
    }

    public function show($orderLabel = null)
    {
        $user = auth()->user();

        if (!$user) {
            // Employee not found
            return response()->json(404, ['message' => 'Пользователь не вошёл в систему']);
        }

        $customFieldsResp = $this->remonline->getClientCustomFields();
        $customFieldsSettings = $customFieldsResp['data'];

        foreach ($customFieldsSettings as $index => $field) {
            if (in_array($field['id'], $this->remonline->legalEntityFields)) {
                $customFieldsSettings[$index]['legal'] = true;
            }
        }
//        dd($customFieldsSettings);

        if ($orderLabel) {
            $virtualNumbers = $user->virtualNumbers()->get(['number', 'description']);
            if ($virtualNumbers->isEmpty()) {
                return response()->json(['message' => 'Нет виртуальных номеров'], 404);
            }
            $virtualNumbers = $virtualNumbers->toArray();

            $order = $this->remonline->getOrders(['id_labels' => [$orderLabel]]);
            $client = $order['data'][0]['client'];
            $clientId = $client['id'];

            $clientData = [
                'name' => $client['name'],
                'address' => $client['address'],
                'email' => $client['email'],
                'notes' => $client['notes'],
                'legalEntity' => $client['juridical'],
            ];

            $clientCustomFields = $client['custom_fields'];

            $phones = [];

            if (isset($client['phone'])) {
                foreach ($client['phone'] as $phone) {
                    $phones[] = [
                        'text' => substr($phone, 0, 7) . '****',
                        'encrypted' => Crypt::encryptString($phone),
                    ];
                }
            }
            return Inertia::render('OrderClient/Show', [
                'createOrder' => false,
                'orderLabel' => $orderLabel,
                'clientData' => $clientData,
                'clientId' => $clientId,
                'phones' => $phones,
                'clientCustomFields' => $clientCustomFields,
                'customFieldsSettings' => $customFieldsSettings,
                'virtualNumbers' => $virtualNumbers,
            ]);
        }

        return Inertia::render('OrderClient/Show', [
            'createOrder' => true,
            'customFieldsSettings' => $customFieldsSettings,
        ]);
    }

    public function updateOrCreate(Request $request)
    {
        $clientData = $request->all();
//        dd($clientData);

        $requestCustomFields = [];
        foreach ($clientData['customFields'] as $customField) {
            if ($customField['type'] === RemonlineApi::REM_BOOLEAN_FIELD_TYPE) {
                $customField['value'] = (bool)$customField['value'];
            } else {
                $customField['value'] = (is_null($customField['value']) ? '' : $customField['value']);
            }

            $requestCustomFields[$customField['id']] = $customField['value'];
        }

        $requestPhones = [];
        foreach ($clientData['phones'] as $phone) {
            if (str_contains($phone['text'], '*')) {
                $requestPhones[] = Crypt::decryptString($phone['encrypted']);
            } else {
                $requestPhones[] = $phone['text'];
            }
        }

        $remonlineClientRequestData = [
            'name' => $clientData['name'],
            'email' => $clientData['email'],
            'notes' => $clientData['notes'],
            'address' => $clientData['address'],

            'phone' => $requestPhones,
        ];

//        dd($remonlineClientRequestData);
        if (!$clientData['clientId']) { // Create client and then order
            $resp = $this->remonline->createClient($remonlineClientRequestData);
            $clientId = $resp['data']['id'];
            $resp = $this->remonline->createOrder([
                'branch_id' => 50230,
                'order_type' => 89790,
                'client_id' => $clientId
            ]);
            $orderId = $resp['data']['id'];
            return Inertia::location('https://web.remonline.app/orders/table/' . $orderId);
        }

        $remonlineClientRequestData['id'] = $clientData['clientId'];
        $remonlineClientRequestData['custom_fields'] = json_encode($requestCustomFields);
        $resp = $this->remonline->updateClient($remonlineClientRequestData);
//        dd($resp);
        return redirect()->route('order.client.show', ['orderLabel' => $clientData['orderLabel']])
            ->with('success', 'Client updated successfully.');

    }

}
