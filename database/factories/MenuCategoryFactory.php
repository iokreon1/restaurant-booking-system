<?php

namespace Database\Factories;

use App\Models\MenuCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MenuCategory>
 */
class MenuCategoryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(3, true),
            'thumbnail_path' => '',
            'sort_order' => fake()->numberBetween(0, 50),
            'status' => MenuCategory::STATUS_ACTIVE,
        ];
    }
}
