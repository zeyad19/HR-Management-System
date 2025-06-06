<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payrolls', function (Blueprint $table) {
  $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->string('month');
            $table->integer('month_days')->nullable(); 
            $table->integer('attended_days')->default(0);
            $table->integer('absent_days')->default(0);
            $table->decimal('total_overtime', 8, 2)->default(0); 
            $table->decimal('total_bonus_amount', 10, 2)->default(0);
            $table->decimal('total_deduction', 8, 2)->default(0);
            $table->decimal('total_deduction_amount', 10, 2)->default(0);
            $table->decimal('net_salary', 12, 2)->nullable();
            $table->timestamps(); 
        
            $table->unique(['employee_id', 'month']);
            $table->softDeletes();
            // $table->foreignId('generated_by')->nullable()->constrained('employees'); // إن أردت تتبع من أنشأه
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
