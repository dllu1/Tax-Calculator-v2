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

        $date = $data['date'];
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
                ['employee_id' => $employeeId, 'work_date' => $date],
                ['type' => $type]
            );

            if ($shifts > 0) {
                Overtime::updateOrCreate(
                    ['employee_id' => $employeeId, 'work_date' => $date],
                    ['shifts' => $shifts]
                );
            } else {
                Overtime::where('employee_id', $employeeId)->whereDate('work_date', $date)->delete();
            }
        }

        return redirect()->route('attendance.index', ['date' => $date])
            ->with('success', 'Đã lưu chấm công ngày ' . Carbon::parse($date)->format('d/m/Y'));
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

        $attendances = Attendance::whereBetween('work_date', [$start, $end])->get()
            ->groupBy(fn($a) => $a->employee_id . '|' . $a->work_date->format('Y-m-d'));

        $overtimes = Overtime::whereBetween('work_date', [$start, $end])->get()
            ->groupBy(fn($o) => $o->employee_id . '|' . $o->work_date->format('Y-m-d'));

        return view('attendance.month', compact(
            'employees', 'year', 'month', 'start', 'end', 'attendances', 'overtimes'
        ));
    }
}