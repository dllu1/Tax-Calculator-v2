<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Overtime;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /**
     * Chấm công theo NGÀY - giao diện đơn giản cho người dùng phổ thông
     */
    public function index(Request $request)
    {
        $date = $request->input('date')
            ? Carbon::parse($request->input('date'))
            : Carbon::today();

        $employees = Employee::where('is_active', true)
            ->orderBy('employee_code')
            ->get();

        $attendances = Attendance::whereDate('work_date', $date)
            ->get()->keyBy('employee_id');

        $overtimes = Overtime::whereDate('work_date', $date)
            ->get()->keyBy('employee_id');

        return view('attendance.index', compact('employees', 'date', 'attendances', 'overtimes'));
    }

    /**
     * Lưu chấm công của 1 ngày cho nhiều nhân viên cùng lúc
     */
    public function saveDay(Request $request)
    {
        $data = $request->validate([
            'date' => ['required', 'date'],
            'rows' => ['array'],
        ]);

        $date = Carbon::parse($data['date'])->startOfDay();
        // Use a plain 'Y-m-d H:i:s' STRING for the updateOrCreate lookup.
        // Passing a Carbon to where() serializes to ISO 8601 UTC (e.g.
        // '2026-05-14T17:00:00.000000Z' in +07:00 timezones), which doesn't
        // match the stored local-time format ('2026-05-15 00:00:00'). The WHERE
        // then misses, INSERT runs, and the unique (employee_id, work_date)
        // constraint blows up with a 500.
        $dateStr = $date->format('Y-m-d H:i:s');
        $rows = $request->input('rows', []);

        foreach ($rows as $employeeId => $row) {
            $type = $row['type'] ?? null;
            $shifts = (int)($row['overtime_shifts'] ?? 0);

            if (!$type) {
                Attendance::where('employee_id', $employeeId)->whereDate('work_date', $date)->delete();
                Overtime::where('employee_id', $employeeId)->whereDate('work_date', $date)->delete();
                continue;
            }

            Attendance::updateOrCreate(
                ['employee_id' => $employeeId, 'work_date' => $dateStr],
                ['type' => $type]
            );

            if ($shifts > 0) {
                Overtime::updateOrCreate(
                    ['employee_id' => $employeeId, 'work_date' => $dateStr],
                    ['shifts' => $shifts]
                );
            } else {
                Overtime::where('employee_id', $employeeId)->whereDate('work_date', $date)->delete();
            }
        }

        $msg = __('Đã lưu chấm công ngày') . ' ' . $date->format('d/m/Y');

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['ok' => true, 'message' => $msg]);
        }

        return redirect()->route('attendance.index', ['date' => $date->format('Y-m-d')])->with('success', $msg);
    }

    /**
     * Xem lưới cả tháng - dành cho người cần nhìn tổng quan
     */
    public function month(Request $request)
    {
        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);

        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $employees = Employee::where('is_active', true)->orderBy('employee_code')->get();

        $attRecords = Attendance::whereBetween('work_date', [$start, $end])->get();
        $otRecords = Overtime::whereBetween('work_date', [$start, $end])->get();

        $attendances = $attRecords->groupBy(fn($a) => $a->employee_id . '|' . $a->work_date->format('Y-m-d'));
        $overtimes = $otRecords->groupBy(fn($o) => $o->employee_id . '|' . $o->work_date->format('Y-m-d'));

        // Per-employee monthly totals for the 3 rightmost summary columns.
        // half + sunday_half count as 0.5 day each — consistent with how they appear in payroll.
        $totals = [];
        foreach ($employees as $emp) {
            $totals[$emp->id] = ['normal' => 0.0, 'sunday' => 0.0, 'overtime' => 0];
        }
        foreach ($attRecords as $a) {
            if (!isset($totals[$a->employee_id])) continue;
            switch ($a->type) {
                case Attendance::TYPE_NORMAL:      $totals[$a->employee_id]['normal'] += 1;   break;
                case Attendance::TYPE_HALF:        $totals[$a->employee_id]['normal'] += 0.5; break;
                case Attendance::TYPE_SUNDAY:      $totals[$a->employee_id]['sunday'] += 1;   break;
                case Attendance::TYPE_SUNDAY_HALF: $totals[$a->employee_id]['sunday'] += 0.5; break;
            }
        }
        foreach ($otRecords as $o) {
            if (!isset($totals[$o->employee_id])) continue;
            $totals[$o->employee_id]['overtime'] += (int) $o->shifts;
        }

        return view('attendance.month', compact(
            'employees', 'year', 'month', 'start', 'end', 'attendances', 'overtimes', 'totals'
        ));
    }
}