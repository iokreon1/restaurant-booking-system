<?php

namespace Database\Factories;

use App\Models\MenuCategory;
use App\Models\MenuItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MenuItem>
 */
class MenuItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => MenuCategory::factory(),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(8),
            'price' => fake()->randomFloat(2, 15000, 125000),
            'thumbnail_path' => '',
            'status' => MenuItem::STATUS_AVAILABLE,
            'sort_order' => fake()->numberBetween(0, 20),
        ];
    }
}
