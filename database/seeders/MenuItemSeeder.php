<?php

namespace Database\Seeders;

use App\Models\MenuCategory;
use App\Models\MenuItem;
use Illuminate\Database\Seeder;

class MenuItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categoryIds = MenuCategory::query()->pluck('id', 'name');

        $itemsByCategory = [
            'Makanan' => [
                ['name' => 'Nasi Goreng Spesial', 'description' => 'Nasi goreng dengan telur, ayam suwir, dan acar segar.', 'price' => 42000, 'status' => MenuItem::STATUS_AVAILABLE, 'image' => 'images/menu/nasi-goreng-spesial.jpg'],
                ['name' => 'Ayam Bakar Madu', 'description' => 'Ayam bakar bumbu madu dengan sambal dan lalapan.', 'price' => 48000, 'status' => MenuItem::STATUS_AVAILABLE, 'image' => 'images/menu/ayam-bakar-madu.jpg'],
                ['name' => 'Sate Ayam', 'description' => 'Sate ayam bumbu kacang disajikan dengan lontong.', 'price' => 40000, 'status' => MenuItem::STATUS_AVAILABLE, 'image' => 'images/menu/sate-ayam.jpg'],
                ['name' => 'Mie Goreng Jawa', 'description' => 'Mie goreng khas Jawa dengan ayam, telur, dan sayuran.', 'price' => 38000, 'status' => MenuItem::STATUS_AVAILABLE, 'image' => 'images/menu/mie-goreng-jawa.jpg'],
                ['name' => 'Sop Buntut', 'description' => 'Sop buntut sapi hangat dengan wortel dan kentang.', 'price' => 65000, 'status' => MenuItem::STATUS_AVAILABLE, 'image' => 'images/menu/sop-buntut.jpg'],
            ],
            'Minuman' => [
                ['name' => 'Es Teh Manis', 'description' => 'Teh manis dingin yang segar.', 'price' => 12000, 'status' => MenuItem::STATUS_AVAILABLE, 'image' => 'images/menu/es-teh-manis.jpg'],
                ['name' => 'Jus Jeruk', 'description' => 'Jus jeruk segar tanpa pemanis buatan.', 'price' => 18000, 'status' => MenuItem::STATUS_AVAILABLE, 'image' => 'images/menu/jus-jeruk.jpg'],
                ['name' => 'Es Kopi Susu', 'description' => 'Kopi susu dingin dengan rasa seimbang.', 'price' => 22000, 'status' => MenuItem::STATUS_AVAILABLE, 'image' => 'images/menu/es-kopi-susu.jpg'],
                ['name' => 'Wedang Jahe', 'description' => 'Minuman jahe hangat dengan gula aren.', 'price' => 16000, 'status' => MenuItem::STATUS_AVAILABLE, 'image' => 'images/menu/wedang-jahe.jpg'],
                ['name' => 'Air Mineral', 'description' => 'Air mineral dingin.', 'price' => 8000, 'status' => MenuItem::STATUS_AVAILABLE, 'image' => 'images/menu/air-mineral.jpg'],
            ],
        ];

        $newItemNames = collect($itemsByCategory)
            ->flatten(1)
            ->pluck('name')
            ->all();

        MenuItem::query()
            ->whereNotIn('name', $newItemNames)
            ->delete();

        foreach ($itemsByCategory as $categoryName => $items) {
            $categoryId = $categoryIds[$categoryName] ?? null;

            if (! $categoryId) {
                continue;
            }

            foreach ($items as $index => $item) {
                MenuItem::query()->updateOrCreate(
                    ['name' => $item['name']],
                    [
                        'category_id' => $categoryId,
                        'name' => $item['name'],
                        'description' => $item['description'],
                        'price' => $item['price'],
                        'thumbnail_path' => $item['image'],
                        'status' => $item['status'],
                        'sort_order' => $index + 1,
                    ]
                );
            }
        }
    }
}
