<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Services\PayrollService;
use App\Services\SettlementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Native\Laravel\Facades\Shell;

/**
 * Opens "print-ready" routes in the user's default browser via Shell::openExternal,
 * so the user gets a real print preview (Ctrl+P) — Electron's native print dialog
 * lacks one. URLs are time-signed (5 min) and bypass session auth in RequirePassword.
 *
 * The render methods just delegate to the existing controllers — the same Blade
 * views (with their @media print CSS) are the source of truth for layout.
 */
class PdfController extends Controller
{
    public function attendanceMonth(int $year, int $month, Request $request, AttendanceController $attendance)
    {
        $request->merge(['year' => $year, 'month' => $month]);
        return $attendance->month($request);
    }

    public function payrollSummary(int $year, int $month, PayrollService $service)
    {
        // Rich 35-col layout for the quarterly tax report — distinct from the screen view.
        $report = $service->buildSummaryReport($year, $month);
        return view('payroll.pdf-summary', $report);
    }

    public function payslip(Employee $employee, int $year, int $month, PayrollController $payroll)
    {
        return $payroll->show($employee, $year, $month);
    }

    public function settlement(string $period, int $year, SettlementService $service)
    {
        $report = $service->build($period, $year);
        return view('settlement.pdf', $report);
    }

    /**
     * Click handler endpoint: validate payload, build a temporarily-signed URL
     * for the matching pdf.print.* route, hand it to Shell::openExternal so the
     * user's default browser opens it. Returns JSON for the frontend toast.
     */
    public function openInBrowser(Request $request)
    {
        $data = $request->validate([
            'type'     => ['required', 'in:attendance-month,payroll-summary,payslip,settlement'],
            'year'     => ['required', 'integer', 'between:2000,2100'],
            'month'    => ['nullable', 'integer', 'between:1,12'],
            'employee' => ['nullable', 'integer', 'exists:employees,id'],
            'period'   => ['nullable', 'in:q1,q2,q3,q4,year'],
        ]);

        $params = ['year' => $data['year']];
        if ($data['type'] === 'settlement') {
            abort_if(empty($data['period']), 422, 'Missing period for settlement');
            $params['period'] = $data['period'];
        } else {
            abort_if(empty($data['month']), 422, 'Missing month');
            $params['month'] = $data['month'];
            if ($data['type'] === 'payslip') {
                abort_if(empty($data['employee']), 422, 'Missing employee for payslip');
                $params['employee'] = $data['employee'];
            }
        }

        $url = URL::temporarySignedRoute(
            "pdf.print.{$data['type']}",
            now()->addMinutes(5),
            $params
        );

        Shell::openExternal($url);

        return response()->json(['ok' => true]);
    }
}
