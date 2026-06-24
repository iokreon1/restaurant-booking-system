<?php

use App\Livewire\Microsite\CatalogMicrosite;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use Livewire\Livewire;

function seedCatalogItems(int $total = 12): void
{
    $appetizer = MenuCategory::query()->create([
        'name' => 'Appetizer',
        'thumbnail_path' => 'images/default.png',
        'sort_order' => 1,
        'status' => MenuCategory::STATUS_ACTIVE,
    ]);

    $mainCourse = MenuCategory::query()->create([
        'name' => 'Main Course',
        'thumbnail_path' => 'images/default.png',
        'sort_order' => 2,
        'status' => MenuCategory::STATUS_ACTIVE,
    ]);

    MenuItem::query()->create([
        'category_id' => $appetizer->id,
        'name' => 'Garden Salad',
        'description' => 'Fresh salad',
        'price' => 85000,
        'thumbnail_path' => 'images/default.png',
        'status' => MenuItem::STATUS_AVAILABLE,
        'sort_order' => 1,
    ]);

    MenuItem::query()->create([
        'category_id' => $mainCourse->id,
        'name' => 'Mushroom Risotto',
        'description' => 'Creamy risotto',
        'price' => 145000,
        'thumbnail_path' => 'images/default.png',
        'status' => MenuItem::STATUS_AVAILABLE,
        'sort_order' => 2,
    ]);
    for ($index = 3; $index <= $total; $index++) {
        MenuItem::query()->create([
            'category_id' => $appetizer->id,
            'name' => "Menu {$index}",
            'description' => "Description {$index}",
            'price' => 80000 + $index,
            'thumbnail_path' => 'images/default.png',
            'status' => MenuItem::STATUS_AVAILABLE,
            'sort_order' => $index,
        ]);
    }
}

it('exposes filtered items as a computed property', function () {
    seedCatalogItems();

    Livewire::test(CatalogMicrosite::class)
        ->assertSee('Garden Salad')
        ->set('selectedCategory', 'main-course')
        ->assertSee('Mushroom Risotto')
        ->assertDontSee('Garden Salad');
});

it('loads 10 products by default then loads more', function () {
    seedCatalogItems(12);

    Livewire::test(CatalogMicrosite::class)
        ->assertSee('Menu 10')
        ->assertDontSee('Menu 11')
        ->call('loadMore')
        ->assertSee('Menu 11')
        ->assertSee('Menu 12');
});

it('filters products by search keyword', function () {
    seedCatalogItems(12);

    Livewire::test(CatalogMicrosite::class)
        ->set('search', 'mushroom')
        ->assertSee('Mushroom Risotto')
        ->assertDontSee('Garden Salad')
        ->assertDontSee('Menu 10');
});
