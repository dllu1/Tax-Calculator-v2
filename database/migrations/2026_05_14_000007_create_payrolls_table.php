<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');

            // Công
            $table->unsignedTinyInteger('normal_days')->default(0);
            $table->unsignedTinyInteger('sunday_days')->default(0);
            $table->unsignedTinyInteger('absent_days')->default(0);
            $table->unsignedSmallInteger('overtime_shifts')->default(0);

            // Các khoản thu nhập
            $table->decimal('day_wage', 15, 2)->default(0)->comment('Lương ngày công');
            $table->decimal('overtime_wage', 15, 2)->default(0)->comment('Lương tăng ca');
            $table->decimal('meal_shift', 15, 2)->default(0)->comment('Tiền ăn giữa ca');
            $table->decimal('meal_overtime', 15, 2)->default(0)->comment('Tiền ăn tăng ca');
            $table->decimal('product_salary', 15, 2)->default(0);
            $table->decimal('diligence', 15, 2)->default(0)->comment('Chuyên cần');
            $table->decimal('taxable_allowances', 15, 2)->default(0);
            $table->decimal('non_taxable_allowances', 15, 2)->default(0);
            $table->decimal('total_income', 15, 2)->default(0)->comment('Tổng lương thực nhận');

            // Thuế và khấu trừ
            $table->decimal('taxable_income', 15, 2)->default(0)->comment('TN tính thuế');
            $table->decimal('personal_deduction', 15, 2)->default(0)->comment('Giảm trừ bản thân');
            $table->decimal('dependent_deduction', 15, 2)->default(0)->comment('Giảm trừ NPT');
            $table->decimal('bhxh_amount', 15, 2)->default(0)->comment('BHXH 10.5%');
            $table->decimal('assessable_income', 15, 2)->default(0)->comment('TN chịu thuế');
            $table->decimal('pit_amount', 15, 2)->default(0)->comment('Thuế TNCN');

            // Khấu trừ khác
            $table->decimal('advance', 15, 2)->default(0)->comment('Tạm ứng');

            // Cuối cùng
            $table->decimal('net_salary', 15, 2)->default(0)->comment('Tiền lương còn lại');

            $table->text('detail')->nullable()->comment('Chi tiết tính toán (JSON)');
            $table->timestamps();

            $table->unique(['employee_id', 'year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};