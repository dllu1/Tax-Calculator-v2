<?php

namespace App\Http\Controllers;

use App\Models\Advance;
use App\Models\Allowance;
use App\Models\Employee;
use App\Models\ProductSalary;
use App\Services\PayrollService;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    public function __construct(private readonly PayrollService $service)
    {
    }

    public function index(Request $request)
    {
        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);

        $employees = Employee::where('is_active', true)->orderBy('employee_code')->get();

        $payrolls = collect();
        foreach ($employees as $employee) {
            $payrolls->push($this->service->calculate($employee, $year, $month));
        }

        return view('payroll.index', compact('payrolls', 'employees', 'year', 'month'));
    }

    public function show(Employee $employee, int $year, int $month)
    {
        $payroll = $this->service->calculate($employee, $year, $month);

        $productSalary = ProductSalary::where([
            'employee_id' => $employee->id, 'year' => $year, 'month' => $month
        ])->first();
        $allowances = Allowance::where([
            'employee_id' => $employee->id, 'year' => $year, 'month' => $month
        ])->get();
        $advances = Advance::where([
            'employee_id' => $employee->id, 'year' => $year, 'month' => $month
        ])->get();

        return view('payroll.show', compact(
            'employee', 'payroll', 'year', 'month',
            'productSalary', 'allowances', 'advances'
        ));
    }

    public function saveProductSalary(Request $request, Employee $employee)
    {
        $data = $request->validate([
            'year' => ['required', 'integer'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'amount' => ['required', 'numeric', 'min:0'],
            'note' => ['nullable', 'string'],
        ]);
        ProductSalary::updateOrCreate(
            ['employee_id' => $employee->id, 'year' => $data['year'], 'month' => $data['month']],
            ['amount' => $data['amount'], 'note' => $data['note'] ?? null]
        );
        return back()->with('success', 'Đã lưu lương sản phẩm');
    }

    public function saveAllowance(Request $request, Employee $employee)
    {
        $data = $request->validate([
            'year' => ['required', 'integer'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:taxable,non_taxable'],
            'amount' => ['required', 'numeric', 'min:0'],
        ]);
        Allowance::create($data + ['employee_id' => $employee->id]);
        return back()->with('success', 'Đã thêm phụ cấp');
    }

    public function deleteAllowance(Allowance $allowance)
    {
        $allowance->delete();
        return back()->with('success', 'Đã xóa phụ cấp');
    }

    public function saveAdvance(Request $request, Employee $employee)
    {
        $data = $request->validate([
            'year' => ['required', 'integer'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'amount' => ['required', 'numeric', 'min:0'],
            'advance_date' => ['nullable', 'date'],
            'note' => ['nullable', 'string'],
        ]);
        Advance::create($data + ['employee_id' => $employee->id]);
        return back()->with('success', 'Đã thêm tạm ứng');
    }

    public function deleteAdvance(Advance $advance)
    {
        $advance->delete();
        return back()->with('success', 'Đã xóa tạm ứng');
    }
}