<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Holiday;
use App\Models\Attendence;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttendenceFullTest extends TestCase
{
    use RefreshDatabase;

    public function test_attendance_all_cases()
    {
        // إعداد HR وتسجيل الدخول
        $password = 'secret123';
        $hr = \App\Models\HR::factory()->create(['email' => 'hr@example.com', 'password' => bcrypt($password)]);
        $token = $this->postJson('/api/hr/login', ['email' => 'hr@example.com', 'password' => $password])->json('token');

        // إعداد قسم وموظف
        $department = Department::factory()->create();
        $employee = Employee::factory()->create([
            'department_id' => $department->id,
            'default_check_in_time' => '09:00',
            'default_check_out_time' => '17:00',
        ]);

        // حضور عادي
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/attendances', [
                'employee_id' => $employee->id,
                'date' => now()->toDateString(),
                'status' => 'Present',
                'checkInTime' => '09:00',
                'checkOutTime' => '17:00',
                'late_minutes' => 0,
                'overtime' => 0,
            ]);
        $response->assertStatus(201);

        // غياب
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/attendances', [
                'employee_id' => $employee->id,
                'date' => now()->addDay()->toDateString(),
                'status' => 'Absent',
                'checkInTime' => '',
                'checkOutTime' => '',
            ]);
        $response->assertStatus(201);

        // تأخير 30 دقيقة
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/attendances', [
                'employee_id' => $employee->id,
                'date' => now()->addDays(2)->toDateString(),
                'status' => 'Late',
                'checkInTime' => '09:30',
                'checkOutTime' => '17:00',
                'late_minutes' => 30,
                'overtime' => 0,
            ]);
        $response->assertStatus(201);

        // أوفر تايم 120 دقيقة
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/attendances', [
                'employee_id' => $employee->id,
                'date' => now()->addDays(3)->toDateString(),
                'status' => 'Present',
                'checkInTime' => '09:00',
                'checkOutTime' => '19:00',
                'late_minutes' => 0,
                'overtime' => 120,
            ]);
        $response->assertStatus(201);

        // تحقق من قاعدة البيانات
        $this->assertDatabaseHas('attendances', [
            'employee_id' => $employee->id,
            'status' => 'Absent',
        ]);
        $this->assertDatabaseHas('attendances', [
            'employee_id' => $employee->id,
            'lateDurationInHours' => 0.5,
        ]);
        $this->assertDatabaseHas('attendances', [
            'employee_id' => $employee->id,
            'overtimeDurationInHours' => 2,
        ]);

        // إضافة إجازة
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/holidays', [
                'date' => now()->addDays(4)->toDateString(),
                'name' => 'اجازة رسمية',
            ]);
        $response->assertStatus(201);

        // تجربة الـ listing
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->get('/api/attendances');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => ['id', 'employee_id', 'date', 'status', 'checkInTime', 'checkOutTime', 'lateDurationInHours', 'overtimeDurationInHours']
        ]);
    }
}