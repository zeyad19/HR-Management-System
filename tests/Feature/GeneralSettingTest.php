<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\GeneralSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GeneralSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_general_setting_can_be_created()
    {
        $setting = GeneralSetting::factory()->create();

        $this->assertDatabaseHas('general_settings', [
            'id' => $setting->id,
            'employee_id' => $setting->employee_id,
        ]);
    }

    public function test_general_setting_can_be_created_via_api()
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

        // إنشاء موظف لربطه بالإعدادات (لو مطلوب)
        $employee = \App\Models\Employee::factory()->create();

        // بيانات الإعدادات العامة
        $settingData = [
            'employee_id' => $employee->id,
            'weekend_days' => ['Friday', 'Saturday'],
            'deduction_type' => 'hours', // أو 'money'
            'deduction_value' => 100,
            'overtime_type' => 'hours', // أو 'money'
            'overtime_value' => 50,
            // أضف أي حقول أخرى مطلوبة
        ];

        // إرسال طلب إضافة الإعدادات مع التوكن
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/settings', $settingData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('general_settings', [
            'employee_id' => $employee->id,
            'weekend_days' => json_encode(['Friday', 'Saturday']),
            'deduction_type' => 'hours', // أو 'money'
            'deduction_value' => 100,
            'overtime_type' => 'hours', // أو 'money'
            'overtime_value' => 50,
            // أضف هنا أي تحقق من الحقول الأخرى إذا لزم الأمر
        ]);
    }
}
