<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\HR;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HRTest extends TestCase
{
    use RefreshDatabase;

    public function test_hr_can_be_created()
    {
        $hr = HR::factory()->create();

        $this->assertDatabaseHas('hrs', [
            'id' => $hr->id,
            'email' => $hr->email,
        ]);
    }

    public function test_hr_can_register_and_login()
    {
        $password = 'secret123';

        // تسجيل HR جديد
        $registerResponse = $this->postJson('/api/hr/register', [
            'name' => 'Test HR',
            'email' => 'hr@example.com',
            'password' => $password,
            'password_confirmation' => $password,
        ]);

        $registerResponse->assertStatus(201);

        // تسجيل الدخول بنفس البيانات
        $loginResponse = $this->postJson('/api/hr/login', [
            'email' => 'hr@example.com',
            'password' => $password,
        ]);

        $loginResponse->assertStatus(200);
        $loginResponse->assertJsonStructure(['token']);
    }
}
