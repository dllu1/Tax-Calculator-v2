<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->date('dob')->nullable()->after('joined_date');
            $table->string('tax_code', 20)->nullable()->after('dob');
            $table->string('id_card', 20)->nullable()->after('tax_code');
            $table->string('phone', 30)->nullable()->after('id_card');
            $table->string('address')->nullable()->after('phone');
        });

        Schema::create('dependents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('relationship', 60)->nullable();
            $table->string('id_card', 20)->nullable();
            $table->string('address')->nullable();
            $table->timestamps();
            $table->index('employee_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dependents');
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['dob', 'tax_code', 'id_card', 'phone', 'address']);
        });
    }
};
