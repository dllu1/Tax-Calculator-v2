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

        // Read-only: load stored payrolls. For active employees without a stored
        // row, fall back to an in-memory compute so the screen has something to
        // show — the user must press "Tính lại" (POST) to persist it.
        $payrolls = collect();
        $hasStale = false;
        foreach ($employees as $employee) {
            $stored = $this->service->find($employee, $year, $month);
            if ($stored) {
                $payrolls->push($stored);
            } else {
                $hasStale = true;
                $payrolls->push($this->service->compute($employee, $year, $month));
            }
        }

        return view('payroll.index', compact('payrolls', 'employees', 'year', 'month', 'hasStale'));
    }

    public function show(Employee $employee, int $year, int $month)
    {
        // Read-only: prefer stored payroll, fall back to in-memory compute.
        $payroll = $this->service->find($employee, $year, $month)
            ?? $this->service->compute($employee, $year, $month);
        $isStale = !$payroll->exists;

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
            'employee', 'payroll', 'year', 'month', 'isStale',
            'productSalary', 'allowances', 'advances'
        ));
    }

    /**
     * POST: recalculate (and persist) all active employees for a month.
     */
    public function recalculate(Request $request)
    {
        $data = $request->validate([
            'year' => ['required', 'integer', 'between:2000,2100'],
            'month' => ['required', 'integer', 'between:1,12'],
        ]);

        $count = $this->service->recalculateMonth($data['year'], $data['month']);

        $msg = __('Đã tính lại bảng lương tháng :m/:y cho :n nhân viên.', [
            'm' => $data['month'], 'y' => $data['year'], 'n' => $count,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['ok' => true, 'message' => $msg]);
        }

        return redirect()
            ->route('payroll.index', ['year' => $data['year'], 'month' => $data['month']])
            ->with('success', $msg);
    }

    /**
     * POST: recalculate (and persist) one employee/month.
     */
    public function recalculateOne(Request $request, Employee $employee, int $year, int $month)
    {
        $this->service->recalculate($employee, $year, $month);
        return $this->respond($request, __('Đã tính lại phiếu lương'));
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
        $this->service->recalculate($employee, (int) $data['year'], (int) $data['month']);
        return $this->respond($request, __('Đã lưu lương sản phẩm'));
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
        $this->service->recalculate($employee, (int) $data['year'], (int) $data['month']);
        return $this->respond($request, __('Đã thêm phụ cấp'));
    }

    public function deleteAllowance(Request $request, Allowance $allowance)
    {
        $employeeId = $allowance->employee_id;
        $year = (int) $allowance->year;
        $month = (int) $allowance->month;
        $allowance->delete();
        $employee = Employee::find($employeeId);
        if ($employee) {
            $this->service->recalculate($employee, $year, $month);
        }
        return $this->respond($request, __('Đã xóa phụ cấp'));
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
        $this->service->recalculate($employee, (int) $data['year'], (int) $data['month']);
        return $this->respond($request, __('Đã thêm tạm ứng'));
    }

    public function deleteAdvance(Request $request, Advance $advance)
    {
        $employeeId = $advance->employee_id;
        $year = (int) $advance->year;
        $month = (int) $advance->month;
        $advance->delete();
        $employee = Employee::find($employeeId);
        if ($employee) {
            $this->service->recalculate($employee, $year, $month);
        }
        return $this->respond($request, __('Đã xóa tạm ứng'));
    }

    private function respond(Request $request, string $message)
    {
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['ok' => true, 'message' => $message]);
        }
        return back()->with('success', $message);
    }
}