<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'  => $this->faker->word(),
            'code'  => 'S',
            'quota' => 100,
        ];
    }
}