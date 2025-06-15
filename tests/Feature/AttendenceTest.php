<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Attendence;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttendenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_attendence_can_be_created()
    {
        $attendence = Attendence::factory()->create();

        $this->assertDatabaseHas('attendances', [
            'id' => $attendence->id,
            'employee_id' => $attendence->employee_id,
        ]);
    }

    public function test_employee_attendence_various_cases_via_api()
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

        // إنشاء موظف
        $department = \App\Models\Department::factory()->create(); // تأكد من وجود قسم
        $employee = \App\Models\Employee::factory()->create([
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
            'weekend_days' => json_encode(['Friday', 'Saturday']),
        ]);

        // بيانات الحضور لأيام مختلفة
        $attendances = [
            [
                'employee_id' => $employee->id,
                'date' => now()->subDays(3)->toDateString(),
                'checkInTime' => '09:00',
                'checkOutTime' => '17:00',
                'status' => 'Present',
                'overtime' => 0,
                'late_minutes' => 0,
            ],
            [
                'employee_id' => $employee->id,
                'date' => now()->subDays(2)->toDateString(),
                'checkInTime' => '09:30',
                'checkOutTime' => '17:00',
                'status' => 'Present', // أو 'Absent'
                'overtime' => 0,
                'late_minutes' => 30,
            ],
            [
                'employee_id' => $employee->id,
                'date' => now()->subDay()->toDateString(),
                'checkInTime' => '09:00',
                'checkOutTime' => '19:00',
                'status' => 'Present', // أو 'Absent'
                'overtime' => 120,
                'late_minutes' => 0,
            ],
            [
                'employee_id' => $employee->id,
                'date' => now()->toDateString(),
                'status' => 'Absent',
                'checkInTime' => '',
                'checkOutTime' => '',
                'overtime' => 0,
                'late_minutes' => 0,
            ],
        ];

        foreach ($attendances as $data) {
            $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                ->postJson('/api/attendances', $data);

            $response->assertStatus(201);
        }

        $this->assertDatabaseCount('attendances', 4);
        $this->assertDatabaseHas('attendances', [
            'employee_id' => $employee->id,
            'status' => 'Absent',
        ]);
        $this->assertDatabaseHas('attendances', [
            'employee_id' => $employee->id,
            'lateDurationInHours' => 0.5, // 30 دقيقة = 0.5 ساعة
        ]);
        $this->assertDatabaseHas('attendances', [
            'employee_id' => $employee->id,
            'overtimeDurationInHours' => 2, // 120 دقيقة = 2 ساعة
        ]);
    }
}
