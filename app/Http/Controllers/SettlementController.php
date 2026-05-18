<?php

namespace App\Http\Controllers;

use App\Services\SettlementService;
use Illuminate\Http\Request;

class SettlementController extends Controller
{
    public function __construct(private readonly SettlementService $service)
    {
    }

    public function index(Request $request)
    {
        $year = (int) $request->input('year', $this->defaultYear());
        $periods = SettlementService::PERIODS;

        return view('settlement.index', compact('year', 'periods'));
    }

    public function show(string $period, Request $request)
    {
        abort_unless(in_array($period, SettlementService::PERIODS, true), 404);
        $year = (int) $request->input('year', $this->defaultYear());

        $report = $this->service->build($period, $year);

        return view('settlement.show', $report);
    }

    /**
     * Năm mặc định = năm hiện tại; nếu đang là tháng 12 thì là năm sau
     * (vì quyết toán Q1 năm sau bắt đầu ngay từ tháng 12 năm nay).
     */
    private function defaultYear(): int
    {
        $now = now();
        return $now->month === 12 ? $now->year + 1 : $now->year;
    }
}
