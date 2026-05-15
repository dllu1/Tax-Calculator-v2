<?php

namespace App\Providers;

use App\Models\Employee;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Native\Laravel\Contracts\ProvidesPhpIni;
use Native\Laravel\Facades\Window;

class NativeAppServiceProvider implements ProvidesPhpIni
{
    /**
     * Executed once the native application has been booted.
     * Use this method to open windows, register global shortcuts, etc.
     */
    public function boot(): void
    {
        $this->seedSampleDataIfEmpty();

        Window::open()
            ->title(config('app.name'))
            ->width(1280)
            ->height(820)
            ->minWidth(1024)
            ->minHeight(640)
            ->rememberState();
    }

    /**
     * On first launch (or after migrate:fresh), populate sample data so the
     * user can test features immediately. Skipped if employees already exist.
     */
    private function seedSampleDataIfEmpty(): void
    {
        try {
            if (! Schema::hasTable('employees')) {
                return; // migrations didn't run yet for some reason
            }
            if (Employee::query()->exists()) {
                return; // already has data
            }
            Artisan::call('db:seed', ['--force' => true]);
        } catch (\Throwable $e) {
            // Don't block the window from opening if seed fails.
            logger()->warning('Auto-seed failed: ' . $e->getMessage());
        }
    }

    /**
     * Return an array of php.ini directives to be set.
     */
    public function phpIni(): array
    {
        return [
            'memory_limit' => '512M',
            'display_errors' => 'stderr',
            'log_errors' => '1',
        ];
    }
}
