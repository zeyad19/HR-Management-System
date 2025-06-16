<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOnDeleteCascadeToEmployeeIdInRelatedTables extends Migration
{
    public function up()
    {
        // تعديل جدول payrolls
        Schema::table('payrolls', function (Blueprint $table) {
            // إزالة القيد الأجنبي الحالي
            $table->dropForeign(['employee_id']);
            // إضافة القيد الأجنبي مع ON DELETE CASCADE
            $table->foreign('employee_id')
                  ->references('id')
                  ->on('employees')
                  ->onDelete('cascade');
        });

        // تعديل جدول attendances (أو attendences إذا كان هذا هو الاسم)
        Schema::table('attendances', function (Blueprint $table) {
            // إزالة القيد الأجنبي الحالي
            $table->dropForeign(['employee_id']);
            // إضافة القيد الأجنبي مع ON DELETE CASCADE
            $table->foreign('employee_id')
                  ->references('id')
                  ->on('employees')
                  ->onDelete('cascade');
        });

        // تعديل جدول general_settings
        Schema::table('general_settings', function (Blueprint $table) {
            // إزالة القيد الأجنبي الحالي
            $table->dropForeign(['employee_id']);
            // إضافة القيد الأجنبي مع ON DELETE CASCADE
            $table->foreign('employee_id')
                  ->references('id')
                  ->on('employees')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        // إعادة القيود الأجنبية بدون ON DELETE CASCADE للتراجع عن الهجرة
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->foreign('employee_id')
                  ->references('id')
                  ->on('employees');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->foreign('employee_id')
                  ->references('id')
                  ->on('employees');
        });

        Schema::table('general_settings', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->foreign('employee_id')
                  ->references('id')
                  ->on('employees');
        });
    }
}