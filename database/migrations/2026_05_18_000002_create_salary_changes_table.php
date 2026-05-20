<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            // Tháng/năm bắt đầu áp dụng mức lương mới. Mọi kỳ lương từ
            // (effective_year, effective_month) trở về sau dùng mức ở đây;
            // kỳ trước vẫn theo mức cũ (fallback xuống employees.basic_salary
            // hoặc đợt salary_change áp dụng trước đó).
            $table->unsignedSmallInteger('effective_year');
            $table->unsignedTinyInteger('effective_month');
            // bhxh_salary và diligence_bonus có thể bỏ trống — khi đó kế thừa
            // đợt áp dụng trước. Riêng basic_salary bắt buộc vì đó là lý do
            // chính tạo đợt thay đổi.
            $table->decimal('basic_salary', 14, 2);
            $table->decimal('bhxh_salary', 14, 2)->nullable();
            $table->decimal('diligence_bonus', 14, 2)->nullable();
            $table->string('note')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'effective_year', 'effective_month'], 'salary_changes_employee_period_unique');
            $table->index(['employee_id', 'effective_year', 'effective_month'], 'salary_changes_lookup_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_changes');
    }
};
