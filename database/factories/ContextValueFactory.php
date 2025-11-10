<?php

namespace Database\Factories;

use App\Models\Context;
use App\Models\ProfileAttribute;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ContextValue>
 */
class ContextValueFactory extends Factory
{
    public function definition(): array
    {
        return [
            'context_id' => Context::factory(),
            'profile_attribute_id' => ProfileAttribute::factory(),
            'value' => fake()->word(),
            'visibility' => 'private',
        ];
    }

    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'visibility' => 'public',
        ]);
    }

    public function protected(): static
    {
        return $this->state(fn (array $attributes) => [
            'visibility' => 'protected',
        ]);
    }

    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'visibility' => 'private',
        ]);
    }
}
