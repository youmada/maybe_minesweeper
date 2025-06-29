<?php

namespace Database\Seeders;

use App\Models\Room;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $playerId = 'test';

        Room::factory(10)->create([
            'owner_id' => $playerId,
        ]
        );
    }
}
