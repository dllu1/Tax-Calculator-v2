<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->decimal('tet_bonus', 15, 2)->default(0)->after('diligence_bonus');
            $table->decimal('annual_leave_pay', 15, 2)->default(0)->after('tet_bonus');
        });

        Schema::table('payrolls', function (Blueprint $table) {
            $table->decimal('tet_bonus', 15, 2)->default(0)->after('diligence');
            $table->decimal('annual_leave_pay', 15, 2)->default(0)->after('tet_bonus');
        });
    }

    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn(['tet_bonus', 'annual_leave_pay']);
        });
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['tet_bonus', 'annual_leave_pay']);
        });
    }
};
