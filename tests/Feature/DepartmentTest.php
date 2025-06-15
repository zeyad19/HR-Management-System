<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DepartmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_department_can_be_created()
    {
        $department = Department::factory()->create();

        $this->assertDatabaseHas('departments', [
            'id' => $department->id,
            'dept_name' => $department->dept_name,
        ]);
    }

    public function test_department_can_be_created_via_api()
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

        // إرسال طلب إنشاء قسم مع التوكن
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/departments', [
                'dept_name' => 'IT Department',
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('departments', [
            'dept_name' => 'IT Department',
        ]);
    }
}
