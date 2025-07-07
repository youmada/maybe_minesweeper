<?php

namespace Database\Factories;

use App\Models\Player;
use App\Utils\UUIDFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlayerFactory extends Factory
{
    protected $model = Player::class;

    public function definition(): array
    {
        return [
            'session_id' => UUIDFactory::generate(),
        ];
    }
}
