<?php

namespace App\Http\Controllers;

use App\Exports\EmployeesTemplateExport;
use App\Imports\EmployeesImport;
use App\Models\Allowance;
use App\Models\Employee;
use App\Models\ProductSalary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

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

    public function destroy(Request $request, Employee $employee)
    {
        $employee->delete();

        $msg = __('Đã xóa nhân viên');

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['ok' => true, 'message' => $msg, 'id' => $employee->id]);
        }

        return redirect()->route('employees.index')->with('success', $msg);
    }

    public function template()
    {
        return Excel::download(new EmployeesTemplateExport(), 'nien-giam-luong--mau-import-nhan-vien.xlsx');
    }

    /**
     * Bước 1: Parse file Excel, phát hiện mã NV trùng.
     *  - Không có dòng trùng → commit luôn.
     *  - Có dòng trùng → lưu cache, redirect kèm flash 'import_preview'
     *    để view tự mở modal xác nhận (giữ cũ / ghi đè).
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv,txt', 'max:5120'],
        ]);

        $import = new EmployeesImport();

        try {
            Excel::import($import, $request->file('file'));
        } catch (\Throwable $e) {
            return redirect()->route('employees.index')
                ->with('error', 'Không đọc được file: ' . $e->getMessage());
        }

        if (empty($import->parsedRows)) {
            return redirect()->route('employees.index')
                ->with('error', 'File không có dòng nhân viên hợp lệ.')
                ->with('import_errors', $import->errors);
        }

        // Không có trùng → commit ngay
        if (empty($import->duplicateCodes)) {
            return $this->applyImport(
                rows: $import->parsedRows,
                extras: $import->parsedExtras,
                duplicateCodes: [],
                action: 'skip',
                rowErrors: $import->errors,
            );
        }

        // Có trùng → lưu cache + flash preview để hiển thị modal
        $key = 'import:employees:' . Str::uuid();
        Cache::put($key, [
            'rows' => $import->parsedRows,
            'extras' => $import->parsedExtras,
            'duplicates' => $import->duplicateCodes,
            'new' => $import->newCodes,
            'errors' => $import->errors,
        ], now()->addMinutes(15));

        $current = Employee::whereIn('employee_code', $import->duplicateCodes)
            ->get(['employee_code', 'full_name', 'position', 'department',
                   'basic_salary', 'bhxh_salary', 'diligence_bonus', 'dependents', 'is_active'])
            ->keyBy('employee_code');

        $incoming = [];
        foreach ($import->duplicateCodes as $code) {
            $incoming[$code] = $import->parsedRows[$code] ?? [];
        }

        return redirect()->route('employees.index')->with('import_preview', [
            'key' => $key,
            'new_count' => count($import->newCodes),
            'duplicate_count' => count($import->duplicateCodes),
            'duplicates' => $import->duplicateCodes,
            'current' => $current->toArray(),
            'incoming' => $incoming,
            'errors' => $import->errors,
        ]);
    }

    /**
     * Bước 2: User đã chọn skip/overwrite trong modal — commit.
     */
    public function importCommit(Request $request)
    {
        $data = $request->validate([
            'key' => ['required', 'string'],
            'action' => ['required', 'in:skip,overwrite'],
        ]);

        $cached = Cache::pull($data['key']);
        if (!$cached) {
            return redirect()->route('employees.index')
                ->with('error', 'Phiên import đã hết hạn. Vui lòng upload lại file.');
        }

        return $this->applyImport(
            rows: $cached['rows'] ?? [],
            extras: $cached['extras'] ?? [],
            duplicateCodes: $cached['duplicates'] ?? [],
            action: $data['action'],
            rowErrors: $cached['errors'] ?? [],
        );
    }

    private function applyImport(array $rows, array $extras, array $duplicateCodes, string $action, array $rowErrors = [])
    {
        $created = 0;
        $updated = 0;
        $skipped = 0;
        $extrasApplied = 0;
        $duplicateSet = array_flip($duplicateCodes);
        $now = now();
        $year = (int) $now->year;
        $month = (int) $now->month;

        foreach ($rows as $code => $payload) {
            $isDuplicate = isset($duplicateSet[$code]);
            try {
                $employee = null;
                if ($isDuplicate) {
                    if ($action === 'skip') {
                        $skipped++;
                        continue;
                    }
                    Employee::where('employee_code', $code)->update($payload);
                    $employee = Employee::where('employee_code', $code)->first();
                    $updated++;
                } else {
                    $employee = Employee::create(['employee_code' => $code] + $payload);
                    $created++;
                }

                // Apply monthly extras (product salary + allowance) for current year/month
                if ($employee && !empty($extras[$code])) {
                    if ($this->applyMonthlyExtras($employee, $extras[$code], $year, $month)) {
                        $extrasApplied++;
                    }
                }
            } catch (\Throwable $e) {
                $rowErrors[] = "Mã {$code}: {$e->getMessage()}";
            }
        }

        $msg = "Đã import: tạo mới {$created}, cập nhật {$updated}, giữ nguyên {$skipped}.";
        if ($extrasApplied > 0) {
            $msg .= " Đã ghi lương SP/phụ cấp tháng {$month}/{$year} cho {$extrasApplied} NV.";
        }
        $redirect = redirect()->route('employees.index')->with('success', $msg);
        if (!empty($rowErrors)) {
            $redirect->with('import_errors', $rowErrors);
        }
        return $redirect;
    }

    /**
     * Ghi/cập nhật lương sản phẩm + phụ cấp cho employee theo tháng/năm hiện tại.
     * Trả về true nếu có ít nhất 1 record được tạo/cập nhật.
     */
    private function applyMonthlyExtras(Employee $employee, array $extras, int $year, int $month): bool
    {
        $touched = false;

        if (!empty($extras['product_salary'])) {
            ProductSalary::updateOrCreate(
                ['employee_id' => $employee->id, 'year' => $year, 'month' => $month],
                ['amount' => (float) $extras['product_salary'], 'note' => 'Import từ Excel']
            );
            $touched = true;
        }

        $name = $extras['allowance_name'] ?? null;
        if ($name) {
            if (!empty($extras['allowance_taxable'])) {
                Allowance::updateOrCreate(
                    [
                        'employee_id' => $employee->id, 'year' => $year, 'month' => $month,
                        'name' => $name, 'type' => Allowance::TYPE_TAXABLE,
                    ],
                    ['amount' => (float) $extras['allowance_taxable']]
                );
                $touched = true;
            }
            if (!empty($extras['allowance_non_taxable'])) {
                Allowance::updateOrCreate(
                    [
                        'employee_id' => $employee->id, 'year' => $year, 'month' => $month,
                        'name' => $name, 'type' => Allowance::TYPE_NON_TAXABLE,
                    ],
                    ['amount' => (float) $extras['allowance_non_taxable']]
                );
                $touched = true;
            }
        }

        return $touched;
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