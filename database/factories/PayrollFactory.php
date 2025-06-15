<?php

namespace Database\Factories;

use App\Models\Payroll;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class PayrollFactory extends Factory
{
    protected $model = Payroll::class;

    public function definition()
    {
        return [
            'employee_id' => Employee::factory(),
            'month' => $this->faker->date('Y-m-01'), // أول يوم في الشهر
            'month_days' => 30,
            'attended_days' => $this->faker->numberBetween(20, 30),
            'absent_days' => $this->faker->numberBetween(0, 5),
            'total_overtime' => $this->faker->randomFloat(2, 0, 20),
            'total_deduction' => $this->faker->randomFloat(2, 0, 10),
            'total_deduction_amount' => $this->faker->randomFloat(2, 0, 500),
            'late_deduction_amount' => $this->faker->randomFloat(2, 0, 100),
            'absence_deduction_amount' => $this->faker->randomFloat(2, 0, 200),
            'total_bonus_amount' => $this->faker->randomFloat(2, 0, 300),
            'net_salary' => $this->faker->randomFloat(2, 1000, 5000),
        ];
    }
}

Payroll::factory()->create();
