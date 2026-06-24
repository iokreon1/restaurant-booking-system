<?php

namespace Database\Seeders;

use App\Models\MenuCategory;
use Illuminate\Database\Seeder;

class MenuCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Makanan',
                'thumbnail_path' => 'images/default.png',
                'sort_order' => 1,
                'status' => MenuCategory::STATUS_ACTIVE,
            ],
            [
                'name' => 'Minuman',
                'thumbnail_path' => 'images/default.png',
                'sort_order' => 2,
                'status' => MenuCategory::STATUS_ACTIVE,
            ],
        ];

        $categoryNames = collect($categories)->pluck('name')->all();

        MenuCategory::query()
            ->whereNotIn('name', $categoryNames)
            ->delete();

        foreach ($categories as $category) {
            MenuCategory::query()->updateOrCreate(
                ['name' => $category['name']],
                $category
            );
        }
    }
}
