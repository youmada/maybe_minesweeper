<?php

namespace Database\Factories;

use App\Models\Room;
use App\Models\RoomState;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class RoomStateFactory extends Factory
{
    protected $model = RoomState::class;

    public function definition(): array
    {
        return [
            'room_id' => Room::factory(),
            'turn_order' => [],
            'flag_limit' => 5,
            'status' => 'waiting',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
