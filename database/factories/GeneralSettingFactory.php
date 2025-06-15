<?php

namespace Database\Factories;

use App\Models\GeneralSetting;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class GeneralSettingFactory extends Factory
{
    protected $model = GeneralSetting::class;

    public function definition()
    {
        return [
            'employee_id' => Employee::factory(),
            'deduction_type' => $this->faker->randomElement(['hours', 'money']),
            'deduction_value' => $this->faker->randomFloat(2, 0, 1000),
            'overtime_type' => $this->faker->randomElement(['hours', 'money']),
            'overtime_value' => $this->faker->randomFloat(2, 0, 500),
            'weekend_days' => json_encode(['Friday', 'Saturday']),
        ];
    }
}
