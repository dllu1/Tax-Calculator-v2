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
            DB::statement("ALTER TABLE attendances MODIFY type ENUM('normal','sunday','absent','leave','half') NOT NULL DEFAULT 'normal'");
        } else {
            // SQLite/PostgreSQL: use portable schema change. Laravel translates
            // enum() to TEXT + CHECK on SQLite and to a named CHECK constraint
            // on PostgreSQL.
            Schema::table('attendances', function (Blueprint $table) {
                $table->enum('type', ['normal', 'sunday', 'absent', 'leave', 'half'])->default('normal')->change();
            });
        }

        Schema::table('payrolls', function (Blueprint $table) {
            $table->unsignedTinyInteger('half_days')->default(0)->after('absent_days');
            $table->decimal('half_day_amount', 15, 2)->default(0)->comment('Half-day pay = diligence_bonus / 2 per half-day')->after('diligence');
        });
    }

    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn(['half_days', 'half_day_amount']);
        });

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement("ALTER TABLE attendances MODIFY type ENUM('normal','sunday','absent','leave') NOT NULL DEFAULT 'normal'");
        } else {
            Schema::table('attendances', function (Blueprint $table) {
                $table->enum('type', ['normal', 'sunday', 'absent', 'leave'])->default('normal')->change();
            });
        }
    }
};
