<?php

namespace Database\Seeders;

use App\Models\Table;
use Illuminate\Database\Seeder;

class TableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tables = [
            ['table_number' => 'A-01', 'capacity' => 2, 'location_description' => 'Indoor', 'status' => Table::STATUS_AVAILABLE],
            ['table_number' => 'A-02', 'capacity' => 4, 'location_description' => 'Indoor', 'status' => Table::STATUS_AVAILABLE],
            ['table_number' => 'A-03', 'capacity' => 2, 'location_description' => 'Indoor', 'status' => Table::STATUS_BOOKED],
            ['table_number' => 'A-04', 'capacity' => 6, 'location_description' => 'Indoor', 'status' => Table::STATUS_AVAILABLE],
            ['table_number' => 'A-05', 'capacity' => 4, 'location_description' => 'Indoor', 'status' => Table::STATUS_AVAILABLE],
            ['table_number' => 'A-06', 'capacity' => 8, 'location_description' => 'Indoor', 'status' => Table::STATUS_MAINTENANCE],
            ['table_number' => 'B-01', 'capacity' => 2, 'location_description' => 'Outdoor', 'status' => Table::STATUS_AVAILABLE],
            ['table_number' => 'B-02', 'capacity' => 4, 'location_description' => 'Outdoor', 'status' => Table::STATUS_AVAILABLE],
            ['table_number' => 'B-03', 'capacity' => 2, 'location_description' => 'Outdoor', 'status' => Table::STATUS_BOOKED],
            ['table_number' => 'B-04', 'capacity' => 6, 'location_description' => 'Outdoor', 'status' => Table::STATUS_AVAILABLE],
            ['table_number' => 'B-05', 'capacity' => 4, 'location_description' => 'Outdoor', 'status' => Table::STATUS_AVAILABLE],
            ['table_number' => 'B-06', 'capacity' => 2, 'location_description' => 'Outdoor', 'status' => Table::STATUS_AVAILABLE],
        ];

        foreach ($tables as $table) {
            Table::query()->updateOrCreate(
                ['table_number' => $table['table_number']],
                $table
            );
        }
    }
}
