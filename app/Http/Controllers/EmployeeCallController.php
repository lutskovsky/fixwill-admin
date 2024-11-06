<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Fixwill\ComagicClient;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class EmployeeCallController extends Controller
{
    public function handle(Request $request)
    {
        // Retrieve the parameters from the GET request
        $contactPhoneNumber = $request->json('phone');

        $virtualNumber = $request->json('virtual_number');


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

        $contactPhoneNumber = Crypt::decryptString($contactPhoneNumber);
//        Log::channel('comagic')->info(print_r($contactPhoneNumber));
        $contactPhoneNumber = preg_replace('/\D/', '', $contactPhoneNumber);

        $client = new ComagicClient(env('COMAGIC_TOKEN'), new \GuzzleHttp\Client());

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
}
