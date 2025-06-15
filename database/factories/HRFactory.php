<?php

namespace Database\Factories;

use App\Models\HR;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class HRFactory extends Factory
{
    protected $model = HR::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => bcrypt('password'),
            'profile_picture' => null, // أو صورة تجريبية
        ];
    }
}
