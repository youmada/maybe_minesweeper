<?php

namespace Database\Factories;

use App\Models\Room;
use App\Models\RoomUser;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class RoomUserFactory extends Factory
{
    protected $model = RoomUser::class;

    public function definition(): array
    {
        return [
            'user_id' => Str::random(32),
            'joined_at' => Carbon::now(),
            'left_at' => Carbon::now(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'room_id' => Room::factory(),
        ];
    }
}
