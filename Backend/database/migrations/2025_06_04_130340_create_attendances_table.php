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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade')->onUpdate('cascade');
            $table->date('date');   
            $table->time('checkInTime')->nullable();
            $table->time('checkOutTime')->nullable();
            $table->decimal('lateDurationInHours',5,2)->default(0);
            $table->decimal('overtimeDurationInHours',5,2)->default(0);
            $table->enum('status',['Present', 'Absent', 'Late', 'On Leave'])->default('Present');
            $table->timestamps();
            $table->unique(['employee_id', 'date']);
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
