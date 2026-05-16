<?php

namespace App\Console\Commands;

use App\Exports\EmployeesTemplateExport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Rebuilds the static employee-import template xlsx.
 *
 * NativePHP ships a static-php-cli PHP build that has no XMLWriter extension,
 * so PhpSpreadsheet can't generate xlsx files at runtime. We pre-build the
 * template once using a full PHP (XAMPP / distro) that does have XMLWriter,
 * commit the result, and the controller just copies the static file at
 * download time. Re-run this whenever EmployeesTemplateExport changes.
 */
class RebuildEmployeeTemplate extends Command
{
    protected $signature = 'app:rebuild-template';
    protected $description = 'Rebuild the static employee-import xlsx template (requires PHP with XMLWriter — use XAMPP php.exe).';

    public function handle(): int
    {
        if (!class_exists('XMLWriter')) {
            $this->error('XMLWriter is missing in this PHP build. Run with a full PHP, e.g.:');
            $this->error('   C:\xampp\php\php.exe artisan app:rebuild-template');
            return 1;
        }

        // resource_path() is fixed to the project bundle; storage_path() is rewritten
        // by NativePHP at boot to point at a user-data dir.
        $out = resource_path('templates/employees-template.xlsx');
        File::ensureDirectoryExists(dirname($out));

        $content = Excel::raw(new EmployeesTemplateExport(), ExcelWriter::XLSX);
        file_put_contents($out, $content);

        $this->info("Wrote {$out} (" . filesize($out) . " bytes).");
        return 0;
    }
}
