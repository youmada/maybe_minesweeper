<?php

namespace Database\Factories;

use App\Domain\Minesweeper\Board;
use App\Models\GameState;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class GameStateFactory extends Factory
{
    protected $model = GameState::class;

    public function definition(): array
    {
        $width = $this->faker->randomElement([5, 10]);
        $height = $this->faker->randomElement([5, 10]);

        return [
            'width' => $width,
            'height' => $height,
            'room_id' => Room::factory(),
            'num_of_mines' => $this->faker->randomNumber(),
            'tile_states' => json_encode((new Board($width, $height))->getBoardState()),
            'is_game_started' => $this->faker->boolean(),
            'is_game_clear' => $this->faker->boolean(),
            'is_game_over' => $this->faker->boolean(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
