<?php

namespace App\Observers;

use App\Models\Dependent;
use App\Models\Employee;

/**
 * Keep Employee.dependents (the integer count used by PayrollService for the
 * personal-deduction calc) in sync with the count of Dependent records.
 */
class DependentObserver
{
    public function saved(Dependent $dependent): void
    {
        $this->syncCount($dependent->employee_id);
    }

    public function deleted(Dependent $dependent): void
    {
        $this->syncCount($dependent->employee_id);
    }

    private function syncCount(?int $employeeId): void
    {
        if (!$employeeId) return;
        $count = Dependent::where('employee_id', $employeeId)->count();
        Employee::where('id', $employeeId)->update(['dependents' => $count]);
    }
}
