<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Fixwill\ComagicClient;

class EmployeeCallController extends Controller
{
    private function decrypt($phone): string
    {
        // Key for decryption
        $secret_key = '2b7e151628aed2a6abf7158809cf4f3c';
        // Base64 decode the encrypted data
        $encrypted_data = base64_decode($phone);

        // Extract the initialization vector and the actual encrypted data
        $iv = substr($encrypted_data, 0, 16);
        $ciphertext = substr($encrypted_data, 16);

        // Decrypt the data
        $decrypted_data = openssl_decrypt($ciphertext, 'aes-256-cbc', $secret_key, OPENSSL_RAW_DATA, $iv);

        // Remove the padding
        $decrypted_data = rtrim($decrypted_data, "\0..\16");

        return $decrypted_data;
    }

    public function handle(Request $request)
    {
        // Retrieve the parameters from the GET request
        $contactPhoneNumber = $request->json('phone');
        $remonlineLogin = $request->json('username');

        // Validate the required parameters
        if (empty($contactPhoneNumber) || empty($remonlineLogin)) {
            return response('Invalid parameters', 400);
        }

        // Search for the employee in the Employees table
        $employee = Employee::where('remonline_login', $remonlineLogin)->first();

        if (!$employee) {
            return response('Employee not found', 404);
        }

        $extension = $employee->internal_phone;
        if (!$employee) {
            return response('Employee not found', 404);
        }

        $contactPhoneNumber = $this->decrypt($contactPhoneNumber);


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
            'virtual_phone_number' => '74954893455',
            'contact' => $contactPhoneNumber,
            'employee' => [
                'id' => $id
            ]
        ];
        $call = $client->call('call', 'start.employee_call', $callParams);

        return response('OK', 200);
    }
}
