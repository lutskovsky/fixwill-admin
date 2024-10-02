<?php

namespace App\Http\Controllers;
use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function getVirtualNumbers($remonline_login)
    {
        // Find the employee by remonline_login
        $employee = Employee::where('remonline_login', $remonline_login)->first();

        if (!$employee) {
            // Employee not found
            return response()->json(['message' => 'Employee not found'], 404);
        }

        // Get the employee's virtual numbers
        $virtualNumbers = $employee->virtualNumbers()->get(['number', 'description']);

        if ($virtualNumbers->isEmpty()) {
            return response()->json(['message' => 'No virtual numbers found for this employee'], 404);
        }

        // Return the virtual numbers
        return response()->json($virtualNumbers);
    }
}
