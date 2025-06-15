<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition()
    {
        return [
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'salary' => $this->faker->randomFloat(2, 1000, 10000),
            'hire_date' => $this->faker->date(),
            'default_check_in_time' => '09:00:00',
            'default_check_out_time' => '17:00:00',
            'gender' => $this->faker->randomElement(['Male', 'Female']),
            'nationality' => $this->faker->country(),
            'national_id' => $this->faker->numerify('##########'),
            'birthdate' => $this->faker->date('Y-m-d', '-18 years'),
            'department_id' => Department::factory(),
            'working_hours_per_day' => 8,
            'weekend_days' => json_encode(['Friday', 'Saturday']),
            'working_hours_per_day' => 8,
            'overtime_value' => 20,
            'deduction_value' => 10,
            'salary_per_hour' => 50,
            'profile_picture' => null,
        ];
    }
}
