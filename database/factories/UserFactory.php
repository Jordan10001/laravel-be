<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => Str::uuid(),
            'email' => fake()->unique()->safeEmail(),
            'name' => fake()->name(),
            'picture_url' => null,
            'google_id' => null,
        ];
    }
}