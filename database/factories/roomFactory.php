<?php

namespace Database\Factories;

use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

class roomFactory extends Factory
{
    protected $model = Room::class;

    public function definition(): array
    {
        return [
            'max_participants' => fake()->randomDigit(),
            'magic_token' => fake()->randomAscii(),
            'is_private' => Arr::random([true, false]),
            'is_active' => true,
        ];
    }
}
