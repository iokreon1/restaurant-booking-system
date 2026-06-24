<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->admin()->create([
            'name' => 'Admin Dapur Nabilah',
            'email' => 'admin@dapurnabilah.com',
        ]);

        User::factory(10)->create();
    }
}
