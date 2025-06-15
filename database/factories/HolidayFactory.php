<?php

namespace Database\Factories;

use App\Models\Holiday;
use Illuminate\Database\Eloquent\Factories\Factory;

class HolidayFactory extends Factory
{
    protected $model = Holiday::class;

    public function definition()
    {
        return [
            'date' => $this->faker->date(),
            'name' => $this->faker->word(),
        ];
    }
}
