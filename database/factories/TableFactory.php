<?php

namespace Database\Factories;

use App\Models\Table;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Table>
 */
class TableFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'table_number' => (string) fake()->unique()->numberBetween(1, 200),
            'capacity' => fake()->numberBetween(2, 10),
            'location_description' => fake()->sentence(),
            'status' => Table::STATUS_AVAILABLE,
        ];
    }
}
