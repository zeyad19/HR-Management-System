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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();

            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();

            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();

            $table->date('hire_date')->nullable();
            $table->decimal('salary', 12, 2)->nullable();

            $table->integer('working_hours_per_day')->default(8);
            $table->time('default_check_in_time')->default('09:00:00');
            $table->time('default_check_out_time')->default('15:00:00');

            $table->string('address')->nullable();
            $table->enum('gender', ['Male', 'Female'])->nullable();
            $table->string('nationality')->nullable();
            $table->date('birthdate')->nullable();

            $table->string('national_id')->unique()->nullable();
            $table->json('weekend_days')->nullable();

            $table->decimal('overtime_value', 8, 2)->default(0);
            $table->decimal('deduction_value', 8, 2)->default(0);
            $table->decimal('salary_per_hour', 8, 2)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
