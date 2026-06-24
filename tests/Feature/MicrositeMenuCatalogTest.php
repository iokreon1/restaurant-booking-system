<?php

use App\Livewire\Microsite\CatalogMicrosite;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use Livewire\Livewire;

function seedMicrositeCatalog(): void
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
}

it('renders the microsite menu catalog with reservation as the next step', function () {
    seedMicrositeCatalog();

    $response = $this->get(route('microsite.menu'));

    $response->assertSuccessful()
        ->assertSee('Cari menu...')
        ->assertSee('Garden Salad')
        ->assertSee(route('microsite.reservation'), false);
});

it('filters menu items by category', function () {
    seedMicrositeCatalog();

    Livewire::test(CatalogMicrosite::class)
        ->set('selectedCategory', 'main-course')
        ->assertSee('Mushroom Risotto')
        ->assertDontSee('Garden Salad');
});
