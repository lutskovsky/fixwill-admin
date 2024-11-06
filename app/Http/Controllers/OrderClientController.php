<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Fixwill\RemonlineApi;
use Inertia\Inertia;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;

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
            'custom_fields' => json_encode($requestCustomFields),
        ];

//        dd($remonlineClientRequestData);
        if (!$clientData['clientId']) { // Create client and then order
            $resp = $this->remonline->createClient($remonlineClientRequestData);
            dd($resp);
        }

        $remonlineClientRequestData['id'] = $clientData['clientId'];
        $resp = $this->remonline->updateClient($remonlineClientRequestData);

        return redirect()->route('order.client.show', ['orderLabel' => $clientData['orderLabel']])
            ->with('success', 'Client updated successfully.');

    }

}
