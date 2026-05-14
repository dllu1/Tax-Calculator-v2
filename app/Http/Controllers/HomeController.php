<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $code = $request->input('code');
        $employee = null;
        if ($code) {
            $employee = Employee::where('employee_code', $code)->first();
        }

        $totalEmployees = Employee::where('is_active', true)->count();

        return view('home', compact('employee', 'code', 'totalEmployees'));
    }

    public function search(Request $request)
    {
        $data = $request->validate(['code' => 'required|string']);
        $employee = Employee::where('employee_code', $data['code'])->first();

        if (!$employee) {
            return redirect()->route('home')->with('error', 'Không tìm thấy nhân viên với mã ' . $data['code']);
        }

        return redirect()->route('payroll.show', [
            'employee' => $employee->id,
            'year' => now()->year,
            'month' => now()->month,
        ]);
    }
}