<?php

namespace App\Repositories;

use App\Domain\Minesweeper\GameState;
use Illuminate\Support\Facades\Redis;
use stdClass;

class RedisMinesweeperRepository implements MinesweeperRepository
{
    private string $prefix = 'minesweeper:game';

    public function saveState(GameState $state, string $gameId): void
    {
        $key = $this->prefix.':'.$gameId;
        $value = json_encode($state->toArray());
        Redis::set($key, $value);
    }

    public function getState(string $gameId): ?stdClass
    {
        $key = $this->prefix.':'.$gameId;

        $value = Redis::get($key);

        return $value ? json_decode($value) : null;
    }

    public function updateState(GameState $state, string $gameId): void
    {
        $key = $this->prefix.':'.$gameId;
        $value = json_encode($state->toArray());
        Redis::set($key, $value);
    }

    public function deleteState(string $gameId): void
    {
        $key = $this->prefix.':'.$gameId;
        Redis::del($key);
    }
}
