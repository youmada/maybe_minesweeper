<?php

namespace Database\Factories;

use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class RoomFactory extends Factory
{
    protected $model = Room::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'owner_id' => $this->faker->word(),
            'magic_link_token' => Str::random(32),
            'max_player' => Arr::random([2, 3, 4]),
            'players' => [],
            'is_private' => $this->faker->boolean(),
            'expire_at' => Carbon::now()->addDays(1),
            'last_activity_at' => Carbon::now(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
