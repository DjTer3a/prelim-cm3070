<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProfileAttribute>
 */
class ProfileAttributeFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->word();

        return [
            'key' => Str::snake($name),
            'name' => ucfirst($name),
            'data_type' => 'string',
            'schema_type' => null,
            'is_system' => false,
        ];
    }

    public function system(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_system' => true,
        ]);
    }

    public function withSchema(string $schemaType): static
    {
        return $this->state(fn (array $attributes) => [
            'schema_type' => $schemaType,
        ]);
    }
}
