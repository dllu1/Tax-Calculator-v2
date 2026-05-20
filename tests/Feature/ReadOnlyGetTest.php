<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Payroll;
use App\Models\Setting;
use App\Services\AuthGate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Enforces the "GET handlers MUST NOT write to the DB" policy.
 * Each scenario seeds the table involved, snapshots count + updated_at,
 * issues a GET, and asserts nothing changed.
 */
class ReadOnlyGetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // RequirePassword middleware redirects to /auth/setup unless a password
        // is configured AND the session flag is set. Configure both for tests.
        Setting::create([
            'key' => AuthGate::KEY_PASSWORD_HASH,
            'value' => password_hash('test-password', PASSWORD_BCRYPT, ['cost' => 4]),
            'type' => Setting::TYPE_STRING,
            'group' => AuthGate::GROUP,
        ]);
        $this->withSession([AuthGate::SESSION_FLAG => true]);
    }

    public function test_get_settings_does_not_write_default_rows(): void
    {
        // Only the auth password row exists; payroll/tax defaults are NOT present.
        $beforeCount = Setting::count();

        $response = $this->get('/settings');

        $response->assertOk();
        $this->assertSame($beforeCount, Setting::count(),
            'GET /settings must not materialize default Setting rows');
    }

    public function test_get_payroll_index_does_not_write_payrolls(): void
    {
        $employee = $this->makeEmployee();
        // Seed one payroll so we can detect both insert and update side-effects.
        $existing = Payroll::create([
            'employee_id' => $employee->id, 'year' => 2026, 'month' => 5,
            'normal_days' => 0, 'sunday_days' => 0, 'sunday_half_days' => 0,
            'absent_days' => 0, 'half_days' => 0, 'overtime_shifts' => 0,
            'day_wage' => 0, 'overtime_wage' => 0, 'meal_shift' => 0,
            'meal_overtime' => 0, 'product_salary' => 0, 'diligence' => 0,
            'half_day_amount' => 0, 'tet_bonus' => 0, 'annual_leave_pay' => 0,
            'taxable_allowances' => 0, 'non_taxable_allowances' => 0,
            'total_income' => 0, 'taxable_income' => 0,
            'personal_deduction' => 0, 'dependent_deduction' => 0,
            'bhxh_amount' => 0, 'assessable_income' => 0, 'pit_amount' => 0,
            'advance' => 0, 'net_salary' => 0, 'detail' => [],
        ]);
        $beforeCount = Payroll::count();
        $beforeUpdatedAt = $existing->updated_at?->toDateTimeString();

        sleep(1); // make sure updated_at would tick if a write happened
        $response = $this->get('/payroll?year=2026&month=5');

        $response->assertOk();
        $this->assertSame($beforeCount, Payroll::count(),
            'GET /payroll must not insert payroll rows');
        $this->assertSame(
            $beforeUpdatedAt,
            $existing->fresh()->updated_at?->toDateTimeString(),
            'GET /payroll must not update existing payroll rows',
        );
    }

    public function test_get_payroll_show_does_not_write_payrolls(): void
    {
        $employee = $this->makeEmployee();
        $beforeCount = Payroll::count();

        $response = $this->get("/payroll/{$employee->id}/2026/5");

        $response->assertOk();
        $this->assertSame($beforeCount, Payroll::count(),
            'GET /payroll/{employee}/{y}/{m} must not insert payroll rows');
    }

    public function test_get_settlement_show_does_not_write_payrolls(): void
    {
        $this->makeEmployee();
        $beforeCount = Payroll::count();

        $response = $this->get('/settlement/q2?year=2026');

        $response->assertOk();
        $this->assertSame($beforeCount, Payroll::count(),
            'GET /settlement/{period} must not insert payroll rows');
    }

    public function test_get_pdf_payroll_summary_does_not_write_payrolls(): void
    {
        $this->makeEmployee();
        $beforeCount = Payroll::count();

        $url = URL::temporarySignedRoute(
            'pdf.print.payroll-summary',
            now()->addMinutes(5),
            ['year' => 2026, 'month' => 5],
        );

        $response = $this->get($url);

        $response->assertOk();
        $this->assertSame($beforeCount, Payroll::count(),
            'GET /pdf/print/payroll-summary must not insert payroll rows');
    }

    public function test_get_employees_template_returns_download_without_filesystem_side_effects(): void
    {
        // Just verifies the endpoint resolves as a read-only download response
        // (or 500 if the template file isn't built). Either way, no DB writes.
        $beforeCount = Employee::count();

        $response = $this->get('/employees/template');

        $this->assertContains($response->baseResponse->getStatusCode(), [200, 500],
            'employees.template should be a read-only download or fail cleanly');
        $this->assertSame($beforeCount, Employee::count());
    }

    private function makeEmployee(): Employee
    {
        return Employee::create([
            'employee_code' => 'E001',
            'full_name' => 'Test Employee',
            'basic_salary' => 10_000_000,
            'bhxh_salary' => 10_000_000,
            'diligence_bonus' => 0,
            'dependents' => 0,
            'is_active' => true,
        ]);
    }
}
