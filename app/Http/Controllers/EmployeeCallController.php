<?php

namespace App\Http\Controllers;

use App\Integrations\ComagicClient;
use App\Models\Courier;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class EmployeeCallController extends Controller
{
    public function handle(Request $request)
    {
        $phoneText = $request->json('phoneText');
        $encryptedPhone = $request->json('encryptedPhone');

        if (str_contains($phoneText, '*')) {
            $contactPhoneNumber = Crypt::decryptString($encryptedPhone);
        } else {
            $contactPhoneNumber = $phoneText;
        }

        $virtualNumber = $request->json('virtualNumber');


        $user = auth()->user();

        if (!$user) {
            // Employee not found
            return response()->json(['message' => 'Пользователь не вошёл в систему'], 404);
        }

        // Get the employee's virtual numbers
        $extension = $user->internal_phone;

        // Validate the required parameters
        if (empty($contactPhoneNumber)) {
            return response('Invalid parameters', 400);
        }

//        // Search for the employee in the Employees table
//        $employee = Employee::where('remonline_login', $remonlineLogin)->first();
//
//        if (!$employee) {
//            return response('Employee not found', 404);
//        }
//
//        $extension = $employee->internal_phone;

//        $contactPhoneNumber = Crypt::decryptString($contactPhoneNumber);
//        Log::channel('comagic')->info(print_r($contactPhoneNumber));
        $contactPhoneNumber = preg_replace('/\D/', '', $contactPhoneNumber);

        $client = new ComagicClient(env('COMAGIC_TOKEN'), new Client());

        $call = $client->call('data', 'get.employees');
        $employees = $call['result']['data'];
        $id = 0;
        foreach ($employees as $employee) {
            if (!empty($employee['extension']) && $employee['extension']['extension_phone_number'] == $extension) {
                $id = $employee['id'];
                break;
            }
        }

        $callParams = [
            'first_call' => 'employee',
            'virtual_phone_number' => ($virtualNumber ?? '74954893455'),
            'contact' => $contactPhoneNumber,
            'employee' => [
                'id' => $id
            ]
        ];

//        dd($callParams);
        $call = $client->call('call', 'start.employee_call', $callParams);
//        Log::channel('comagic')->info(print_r($call));
        return response('OK', 200);
    }

    public static function courierCall($contactPhoneNumber, $chatId)
    {
//        $virtualNumber = '74954893455';


        $courier = Courier::where('chat_id', $chatId)->first();

        if (!$courier) {
            // Employee not found
            return response()->json(['message' => 'Пользователь не вошёл в систему'], 404);
        }

        if (!($sip = $courier->sipLine)) {
            throw new Exception("Не привязана SIP-линия к курьеру");
        }

        if (empty($contactPhoneNumber)) {
            return response('Invalid parameters', 400);
        }

        $contactPhoneNumber = preg_replace('/\D/', '', $contactPhoneNumber);

        $client = new ComagicClient(env('COMAGIC_TOKEN'), new Client());
        $callParams = [
            'first_call' => 'employee',
            'virtual_phone_number' => ($sip->virtual_number ?? '74954893455'),
            'contact' => $contactPhoneNumber,
            'employee' => [
                'id' => $sip->employee_id
            ]
        ];

        $result = $client->call('call', 'start.employee_call', $callParams);

        Log::channel('comagic')->info('start.employee_call:');
        Log::channel('comagic')->info($callParams);
        Log::channel('comagic')->info($result);

        return $result;
    }

    public function scenarioCall(Request $request)
    {
        $number = $request->input('number');

        $client = new ComagicClient(env('COMAGIC_TOKEN'), new Client());
        $callParams = [
            'first_call' => 'employee',
            'virtual_phone_number' => '79326982662',
            'contact' => $number,
            'scenario_id' => 545220
        ];

        $result = $client->call('call', 'start.scenario_call', $callParams);
        Log::channel('comagic')->info('start.scenario_call:');
        Log::channel('comagic')->info($callParams);
        Log::channel('comagic')->info($result);

        return response('OK', 200);

    }
}
