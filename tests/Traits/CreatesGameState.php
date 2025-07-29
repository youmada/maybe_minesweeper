<?php

namespace Tests\Traits;

use App\Domain\Minesweeper\GameService;
use Illuminate\Support\Facades\Redis;

trait CreatesGameState
{
    /**
     * Redis にゲームデータをセットする
     */
    protected function seedGameState(int $roomId, array $overwrites = []): void
    {
        $defaults = [
            'width' => 5,
            'height' => 5,
            'numOfMines' => 10,
            'tileStates' => GameService::createBoard(5, 5)->toArray(),
            'isGameStarted' => true,
            'isGameOver' => false,
            'isGameClear' => false,
            'visitedTiles' => [],
        ];

        $payload = array_merge($defaults, $overwrites);

        Redis::set(
            "minesweeper:game:{$roomId}",
            json_encode($payload)
        );
    }
}
