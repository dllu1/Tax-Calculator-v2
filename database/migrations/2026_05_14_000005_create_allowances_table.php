<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('allowances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->string('name')->comment('VD: Phụ cấp xăng xe, điện thoại...');
            $table->enum('type', ['taxable', 'non_taxable'])->comment('Có/không chịu thuế TNCN');
            $table->decimal('amount', 15, 2)->default(0);
            $table->timestamps();

            $table->index(['employee_id', 'year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('allowances');
    }
};