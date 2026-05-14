<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::query();
        if ($s = $request->input('q')) {
            $query->where(function ($q) use ($s) {
                $q->where('employee_code', 'like', "%$s%")
                  ->orWhere('full_name', 'like', "%$s%");
            });
        }
        $employees = $query->orderBy('employee_code')->paginate(15)->withQueryString();
        return view('employees.index', compact('employees'));
    }

    public function create()
    {
        return view('employees.create', ['employee' => new Employee()]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        Employee::create($data);
        return redirect()->route('employees.index')->with('success', 'Đã thêm nhân viên');
    }

    public function edit(Employee $employee)
    {
        return view('employees.create', compact('employee'));
    }

    public function update(Request $request, Employee $employee)
    {
        $data = $this->validateData($request, $employee->id);
        $employee->update($data);
        return redirect()->route('employees.index')->with('success', 'Đã cập nhật');
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();
        return redirect()->route('employees.index')->with('success', 'Đã xóa nhân viên');
    }

    private function validateData(Request $request, ?int $id = null): array
    {
        $unique = 'unique:employees,employee_code' . ($id ? ",$id" : '');
        return $request->validate([
            'employee_code' => ['required', 'string', 'max:20', $unique],
            'full_name' => ['required', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'joined_date' => ['nullable', 'date'],
            'basic_salary' => ['required', 'numeric', 'min:0'],
            'bhxh_salary' => ['required', 'numeric', 'min:0'],
            'diligence_bonus' => ['required', 'numeric', 'min:0'],
            'dependents' => ['required', 'integer', 'min:0', 'max:20'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }
}