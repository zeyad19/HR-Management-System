<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('general_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id')->unique();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');

            $table->enum('deduction_type', ['hours', 'money'])->default('money');
            $table->decimal('deduction_value', 10, 2)->default(0);
            $table->enum('overtime_type', ['hours', 'money'])->default('money');
            $table->decimal('overtime_value', 10, 2)->default(0);
            $table->json('weekend_days')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('general_settings');
    }
};
