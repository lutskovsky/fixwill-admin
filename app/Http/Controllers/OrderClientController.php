<?php

namespace App\Http\Controllers;

use App\Integrations\RemonlineApi;
use Doctrine\DBAL\Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class OrderClientController extends Controller
{
    protected RemonlineApi $remonline;

    public function __construct(RemonlineApi $remonline)
    {
        $this->remonline = $remonline;
    }

    public function getClientById($clientId)
    {
        $clients = $this->remonline->getClients(['ids' => [$clientId]]);
        if (!$clients['data']) {
            abort(404);
        }
        $clientData = $clients['data'][0];
        return $this->show($clientData);
    }

    public function getClientByOrderLabel($orderLabel)
    {
        $order = $this->remonline->getOrders(['id_labels' => [$orderLabel]]);
        $clientData = $order['data'][0]['client'];

        if (!$clientData) {
            return response("<h1>В заказе нет клиента!</h1>");
        }
        return $this->show($clientData);
    }

    public function orderAndClientCreateForm()
    {
        return Inertia::render('OrderClient/Show', [
            'createOrder' => true,
            'customFieldsSettings' => $this->getCustomFieldsSettings(),
        ]);
    }


    protected function show($client)
    {
        $user = auth()->user();

        if (!$user) {
            // Employee not found
            return response()->json(404, ['message' => 'Пользователь не вошёл в систему']);
        }

        $virtualNumbers = $user->virtualNumbers()->get(['number', 'description']);
        $virtualNumbers = $virtualNumbers->toArray();

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
            'clientId' => $clientId,
            'createOrder' => false,
            'clientData' => $clientData,
            'phones' => $phones,
            'clientCustomFields' => $clientCustomFields,
            'customFieldsSettings' => $this->getCustomFieldsSettings(),
            'virtualNumbers' => $virtualNumbers,
        ]);
    }

    public function updateClient(Request $request, $clientId)
    {
        $remonlineClientRequestData = $this->prepareClientRequest($request);
        $remonlineClientRequestData['id'] = $clientId;
//        $remonlineClientRequestData['custom_fields'] = json_encode($requestCustomFields);
        $resp = $this->remonline->updateClient($remonlineClientRequestData);
        return redirect()->route('client.show', ['clientId' => $clientId])
            ->with('success', 'Client updated successfully.');
    }

    public function createClientAndOrder(Request $request)
    {
        $remonlineClientRequestData = $this->prepareClientRequest($request);
        $resp = $this->remonline->createClient($remonlineClientRequestData);
        $clientId = $resp['data']['id'];
        return $this->createOrder($clientId);
    }

    public function createOrder(mixed $clientId, $orderType = 89790): mixed
    {
        $resp = $this->remonline->createOrder([
            'branch_id' => 50230,
            'order_type' => $orderType,
            'client_id' => $clientId
        ]);

        return Inertia::location('https://web.roapp.io/orders/table/' . $resp['data']['id']);
    }

//    public function updateOrCreate(Request $request)
//    {
//        $remonlineClientRequestData = $this->prepareClientRequest($request);
//
//        if (!$clientData['clientId']) { // Create client and then order
//            $resp = $this->remonline->createClient($remonlineClientRequestData);
//            $clientId = $resp['data']['id'];
//            $orderId = $this->createOrder($clientId);
//            return Inertia::location('https://web.roapp.io/orders/table/' . $orderId);
//        }
//
//
//    }

    public function searchForm()
    {
        return Inertia::render('OrderClient/Search');
    }

    /**
     * Handle the search request and fetch clients from a third-party API.
     */
    public function search(Request $request)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
        ]);

        $query = array_filter($validated); // Remove null values


        $response = $this->remonline->getClients([
            "names" => [$query['name'] ?? null],
            "phones" => [$query['phone'] ?? null],
            "emails" => [$query['email'] ?? null],
        ]);

        $clients = $response['data'];


        // Return the view with the search results
        return Inertia::render('OrderClient/Search', [
            'clients' => $clients,
            'filters' => $query,
        ]);
    }

    /**
     * @return array|mixed
     */
    protected function getCustomFieldsSettings(): mixed
    {
        $customFieldsSettings = $this->remonline->getClientCustomFields()['data'];

        foreach ($customFieldsSettings as $index => $field) {
            if (in_array($field['id'], $this->remonline->legalEntityFields)) {
                $customFieldsSettings[$index]['legal'] = true;
            }
        }
        return $customFieldsSettings;
    }


    /**
     * @param Request $request
     * @return array
     */
    protected function prepareClientRequest(Request $request): array
    {
        $clientData = $request->all();

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
                try {
                    $requestPhones[] = Crypt::decryptString($phone['encrypted']);
                } catch (Exception $e) {
                    Log::error($clientData['name'] . " / decrypt error. plain:". $phone['text'] . ", encrypted:" . $phone['encrypted']);
                }

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
        return $remonlineClientRequestData;
    }

}
