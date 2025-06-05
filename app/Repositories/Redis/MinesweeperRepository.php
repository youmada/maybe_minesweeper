<?php

namespace App\Repositories\Redis;

use App\Domain\Minesweeper\GameState;
use App\Repositories\Interfaces\Game;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use stdClass;

class Minesweeper implements Game
{
    private string $prefix = 'minesweeper:game';

    /**
     * @throws Exception
     */
    public function saveState(GameState $state, string $gameId): void
    {
        $key = $this->prefix.':'.$gameId;
        $value = json_encode($state->toArray());
        try {
            Redis::set($key, $value);
        } catch (Exception $e) {
            Log::error("Redis saveState error for key {$key}: ".$e->getMessage());
            throw $e;
        }
    }

    public function getState(string $gameId): ?stdClass
    {
        $key = $this->prefix.':'.$gameId;

        try {
            $value = Redis::get($key);

        } catch (Exception $e) {
            Log::error("Redis getState error for key {$key}: ".$e->getMessage());

            return null;
        }

        return $value ? json_decode($value) : null;
    }

    /**
     * @throws Exception
     */
    public function updateState(GameState $state, string $gameId): void
    {
        $key = $this->prefix.':'.$gameId;
        $value = json_encode($state->toArray());
        try {
            Redis::set($key, $value);
        } catch (Exception $e) {
            Log::error("Redis updateState error for key {$key}: ".$e->getMessage());
            throw $e;
        }

    }

    /**
     * @throws Exception
     */
    public function deleteState(string $gameId): void
    {
        $key = $this->prefix.':'.$gameId;
        try {
            Redis::del($key);
        } catch (Exception $e) {
            Log::error("Redis deleteState error for key {$key}: ".$e->getMessage());
            throw $e;
        }

    }
}
