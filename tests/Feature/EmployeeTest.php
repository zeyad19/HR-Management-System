<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EmployeeTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_be_created()
    {
        $employee = Employee::factory()->create([
            'gender' => 'Male', // أو 'Female'
            'birthdate' => now()->subYears(25)->toDateString(),
            'working_hours_per_day' => 8,
            // أضف أي حقل عليه تحقق أو مطلوب
        ]);

        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'email' => $employee->email,
            
        ]);
    }

    public function test_employee_can_be_created_via_api()
    {
        // إنشاء HR وتسجيل دخوله للحصول على التوكن
        $password = 'secret123';
        $hr = \App\Models\HR::factory()->create([
            'email' => 'hr@example.com',
            'password' => bcrypt($password),
        ]);

        $loginResponse = $this->postJson('/api/hr/login', [
            'email' => 'hr@example.com',
            'password' => $password,
        ]);

        $token = $loginResponse->json('token');

        // إنشاء قسم وربطه بالموظف (لو مطلوب)
        $department = \App\Models\Department::factory()->create();

        // بيانات الموظف الجديد
        $employeeData = [
            'first_name' => 'Ahmed',
            'last_name' => 'Ali',
            'email' => 'ahmed.ali@example.com',
            'phone' => '01234567890',
            'address' => 'Cairo',
            'salary' => 5000,
            'hire_date' => now()->toDateString(),
            'default_check_in_time' => '09:00',
            'default_check_out_time' => '17:00',
            'gender' => 'Male',
            'nationality' => 'Egyptian',
            'national_id' => '12345678901234',
            'birthdate' => now()->subYears(25)->toDateString(),
            'department_id' => $department->id,
            'working_hours_per_day' => 8,
            'weekend_days' => ['Friday', 'Saturday'],
        ];

        // إرسال طلب إضافة الموظف مع التوكن
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/employees', $employeeData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('employees', [
            'email' => 'ahmed.ali@example.com',
        ]);
    }
}
