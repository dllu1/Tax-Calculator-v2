<?php

namespace Tests\Feature;

use App\Models\Allowance;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\Setting;
use App\Services\AuthGate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Verifies the explicit POST recalculate endpoints — the only routes
 * allowed to persist payroll rows under the GET-is-read-only policy.
 */
class PostRecalculateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::create([
            'key' => AuthGate::KEY_PASSWORD_HASH,
            'value' => password_hash('test-password', PASSWORD_BCRYPT, ['cost' => 4]),
            'type' => Setting::TYPE_STRING,
            'group' => AuthGate::GROUP,
        ]);
        $this->withSession([AuthGate::SESSION_FLAG => true]);
    }

    public function test_post_payroll_recalculate_persists_for_all_active_employees(): void
    {
        $a = $this->makeEmployee('E001');
        $b = $this->makeEmployee('E002');
        $this->makeEmployee('E003', false); // inactive — must be skipped

        $this->assertSame(0, Payroll::count());

        $response = $this->post('/payroll/recalculate', [
            'year' => 2026, 'month' => 5,
        ]);

        $response->assertRedirect();
        $this->assertSame(2, Payroll::count(),
            'POST /payroll/recalculate should persist one row per active employee');
        $this->assertDatabaseHas('payrolls', ['employee_id' => $a->id, 'year' => 2026, 'month' => 5]);
        $this->assertDatabaseHas('payrolls', ['employee_id' => $b->id, 'year' => 2026, 'month' => 5]);
    }

    public function test_post_settlement_recalculate_persists_payrolls_for_period_months(): void
    {
        $emp = $this->makeEmployee('E001');

        $response = $this->post('/settlement/q2/recalculate', ['year' => 2026]);
        $response->assertRedirect();

        // Q2 covers months 3, 4, 5.
        foreach ([3, 4, 5] as $m) {
            $this->assertDatabaseHas('payrolls',
                ['employee_id' => $emp->id, 'year' => 2026, 'month' => $m]);
        }
    }

    public function test_post_allowance_store_recalculates_affected_payroll(): void
    {
        $emp = $this->makeEmployee('E001');
        $this->assertSame(0, Payroll::count());

        $response = $this->post("/employees/{$emp->id}/allowance", [
            'year' => 2026, 'month' => 5,
            'name' => 'Xăng xe', 'type' => 'taxable', 'amount' => 500_000,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('allowances', [
            'employee_id' => $emp->id, 'year' => 2026, 'month' => 5, 'amount' => 500_000,
        ]);
        // The save handler should trigger a recalc → payroll row materializes.
        $this->assertDatabaseHas('payrolls', [
            'employee_id' => $emp->id, 'year' => 2026, 'month' => 5,
        ]);
    }

    public function test_post_settings_update_creates_default_rows_when_missing(): void
    {
        // Only the auth row exists; no payroll defaults yet.
        $authOnly = Setting::count();

        $response = $this->put('/settings', [
            'settings' => ['payroll.standard_days' => 24],
        ]);

        $response->assertRedirect();
        $this->assertGreaterThan($authOnly, Setting::count(),
            'POST settings.update must materialize defaults so the row exists before writing');
        $this->assertSame('24', Setting::where('key', 'payroll.standard_days')->value('value'));
    }

    private function makeEmployee(string $code, bool $active = true): Employee
    {
        return Employee::create([
            'employee_code' => $code,
            'full_name' => "Employee {$code}",
            'basic_salary' => 10_000_000,
            'bhxh_salary' => 10_000_000,
            'diligence_bonus' => 0,
            'dependents' => 0,
            'is_active' => $active,
        ]);
    }
}
