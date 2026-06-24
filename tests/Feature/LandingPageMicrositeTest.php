<?php

use App\Models\MenuCategory;
use App\Models\MenuItem;

it('shows featured menu items from the database on the landing microsite', function () {
    $category = MenuCategory::query()->create([
        'name' => 'Main Course',
        'thumbnail_path' => 'images/default.png',
        'sort_order' => 1,
        'status' => MenuCategory::STATUS_ACTIVE,
    ]);

    MenuItem::query()->create([
        'category_id' => $category->id,
        'name' => 'Ayam Bakar Madu',
        'description' => 'Ayam bakar dengan glaze madu dan sambal segar.',
        'price' => 45000,
        'thumbnail_path' => 'images/default.png',
        'status' => MenuItem::STATUS_AVAILABLE,
        'sort_order' => 1,
    ]);

    MenuItem::query()->create([
        'category_id' => $category->id,
        'name' => 'Es Teh Melati',
        'description' => 'Teh melati dingin dengan aroma bunga yang ringan.',
        'price' => 18000,
        'thumbnail_path' => 'images/default.png',
        'status' => MenuItem::STATUS_SOLDOUT,
        'sort_order' => 2,
    ]);

    MenuItem::query()->create([
        'category_id' => $category->id,
        'name' => 'Menu Rahasia',
        'description' => 'Menu ini seharusnya tidak tampil.',
        'price' => 99000,
        'thumbnail_path' => 'images/default.png',
        'status' => MenuItem::STATUS_INACTIVE,
        'sort_order' => 3,
    ]);

    $response = $this->get(route('home'));

    $response->assertSuccessful();
    $response->assertSee('Ayam Bakar Madu', false);
    $response->assertSee('Es Teh Melati', false);
    $response->assertDontSee('Menu Rahasia', false);
    $response->assertSee('Rp 18.000', false);
    $response->assertSee(route('microsite.menu'), false);
});
