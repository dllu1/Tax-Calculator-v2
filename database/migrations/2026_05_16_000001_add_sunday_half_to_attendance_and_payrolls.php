<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement("ALTER TABLE attendances MODIFY type ENUM('normal','sunday','absent','leave','half','sunday_half') NOT NULL DEFAULT 'normal'");
        } else {
            Schema::table('attendances', function (Blueprint $table) {
                $table->enum('type', ['normal', 'sunday', 'absent', 'leave', 'half', 'sunday_half'])->default('normal')->change();
            });
        }

        Schema::table('payrolls', function (Blueprint $table) {
            $table->unsignedTinyInteger('sunday_half_days')->default(0)->after('sunday_days');
        });
    }

    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn('sunday_half_days');
        });

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement("ALTER TABLE attendances MODIFY type ENUM('normal','sunday','absent','leave','half') NOT NULL DEFAULT 'normal'");
        } else {
            Schema::table('attendances', function (Blueprint $table) {
                $table->enum('type', ['normal', 'sunday', 'absent', 'leave', 'half'])->default('normal')->change();
            });
        }
    }
};
