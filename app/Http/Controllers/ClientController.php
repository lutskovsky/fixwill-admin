<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function create(Request $request)
    {
        $client = new Client();
        $response = $client->post(env('THIRD_PARTY_API_URL') . '/clients', [
            'json' => $request->all()
        ]);

        return response()->json(json_decode($response->getBody()->getContents(), true), $response->getStatusCode());
    }
}
