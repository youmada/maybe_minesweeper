<?php

namespace Database\Factories;

use App\Models\rooms;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

class roomsFactory extends Factory
{
    protected $model = rooms::class;

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
