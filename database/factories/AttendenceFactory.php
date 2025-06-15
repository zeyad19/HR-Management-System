<?php

namespace Database\Factories;

use App\Models\Attendence;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendenceFactory extends Factory
{
    protected $model = Attendence::class;

    public function definition()
    {
        return [
            'employee_id' => Employee::factory(),
            'date' => $this->faker->date(),
            'checkInTime' => '09:00:00',
            'checkOutTime' => '17:00:00',
            'lateDurationInHours' => 0,
            'overtimeDurationInHours' => 1,
            'status' => $this->faker->randomElement(['Present', 'Absent', 'Late']),
        ];
    }
}
