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
        // Mở cửa sổ TRƯỚC: Electron dựng cửa sổ song song với migrate/seed bên
        // dưới. Request tải trang sẽ chờ tới khi boot() xong (PHP server xử lý
        // từng request một) nên trang không bao giờ thấy DB chưa migrate.
        Window::open()
            ->title(config('app.name'))
            ->width(1280)
            ->height(820)
            ->minWidth(1024)
            ->minHeight(640)
            ->rememberState();

        $this->migrateIfNeeded();
        $this->seedSampleDataIfEmpty();
    }

    /**
     * Chạy migration khi khởi động nếu còn migration chưa chạy. Cần thiết để khi
     * NGƯỜI DÙNG CŨ cập nhật lên bản mới, database hiện có trong thư mục dữ liệu
     * của họ được bổ sung các bảng/cột mới (vd bảng `leaves`, cột nghỉ phép trong
     * `payrolls`) — vì NativePHP chỉ copy DB đóng gói ở LẦN ĐẦU cài, không ghi đè
     * DB người dùng khi nâng cấp.
     *
     * Chỉ so tên file migration với bảng `migrations` (~2ms) thay vì luôn gọi
     * `migrate` (~240ms mỗi lần mở app dù không có gì để chạy).
     */
    private function migrateIfNeeded(): void
    {
        try {
            $migrator = app('migrator');
            $files = array_keys($migrator->getMigrationFiles(database_path('migrations')));
            $ran = $migrator->repositoryExists() ? $migrator->getRepository()->getRan() : [];
            if (array_diff($files, $ran) === []) {
                return;
            }

            Artisan::call('migrate', ['--force' => true]);
        } catch (\Throwable $e) {
            // Không chặn cửa sổ mở nếu migrate lỗi — chỉ ghi log.
            logger()->warning('Auto-migrate failed: ' . $e->getMessage());
        }
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
