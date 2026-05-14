<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_code', 20)->unique();
            $table->string('full_name');
            $table->string('position')->nullable();
            $table->string('department')->nullable();
            $table->date('joined_date')->nullable();
            $table->decimal('basic_salary', 15, 2)->default(0);
            $table->decimal('bhxh_salary', 15, 2)->default(0)->comment('Mức lương đóng BHXH');
            $table->decimal('diligence_bonus', 15, 2)->default(0)->comment('Tiền chuyên cần khi đủ công');
            $table->unsignedTinyInteger('dependents')->default(0)->comment('Số người phụ thuộc');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};