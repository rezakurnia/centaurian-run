<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;

class EmployeeController extends Controller
{
    /**
     * Look a NIP up in the employee master so the registration form can show the
     * matching name, or flag the NIP as unknown before the form is submitted.
     */
    public function show(string $nip)
    {
        $employee = Employee::where('nip', trim($nip))->first();

        if (! $employee) {
            return response()->json([
                'message' => 'NIP tidak terdaftar sebagai guru/karyawan Alcent.',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'message' => 'NIP terdaftar.',
            'data' => [
                'nip' => $employee->nip,
                'name' => $employee->name,
            ],
        ]);
    }
}
