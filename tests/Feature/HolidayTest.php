<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Holiday;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HolidayTest extends TestCase
{
    use RefreshDatabase;

    public function test_holiday_can_be_created()
    {
        $holiday = Holiday::factory()->create();

        $this->assertDatabaseHas('holidays', [
            'id' => $holiday->id,
            'name' => $holiday->name,
        ]);
    }

    public function test_holiday_can_be_created_via_api()
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

        // بيانات الإجازة (مثلاً يوم 10 في شهر 7)
        $holidayData = [
            'name' => 'اجازة صيفية',
            'date' => now()->setMonth(7)->setDay(10)->toDateString(),
            // أضف أي حقول أخرى مطلوبة في holidays
        ];

        // إرسال طلب إضافة الإجازة مع التوكن
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/holidays', $holidayData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('holidays', [
            'name' => 'اجازة صيفية',
            'date' => now()->setMonth(7)->setDay(10)->toDateString() . ' 00:00:00',
        ]);
    }
}
