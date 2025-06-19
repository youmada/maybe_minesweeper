<?php

namespace App\Repositories\Redis;

use App\Domain\Minesweeper\GameState;
use App\Domain\Minesweeper\GameState as State;
use App\Repositories\Interfaces\GameRepositoryInterface;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class MinesweeperRepository implements GameRepositoryInterface
{
    private string $prefix = 'minesweeper:game';

    /**
     * @throws Exception
     */
    public function saveState(GameState $state, string $roomId): void
    {
        $key = $this->prefix.':'.$roomId;
        $value = json_encode($state->toArray());
        try {
            Redis::set($key, $value);
        } catch (Exception $e) {
            Log::error("Redis saveState error for key {$key}: ".$e->getMessage());
            throw $e;
        }
    }

    public function getState(string $roomId): ?GameState
    {
        $key = $this->prefix.':'.$roomId;

        try {
            $value = Redis::get($key);

        } catch (Exception $e) {
            Log::error("Redis getState error for key {$key}: ".$e->getMessage());

            return null;
        }

        $decoded = json_decode($value, true);

        $state = State::fromPrimitive(
            $decoded['tileStates'],
            $decoded['width'],
            $decoded['height'],
            $decoded['numOfMines'],
            $decoded['isGameStarted'],
            $decoded['isGameClear'],
            $decoded['isGameOver'],
        );

        return $value ? $state : null;
    }

    /**
     * @throws Exception
     */
    public function updateState(GameState $state, string $roomId): void
    {
        $key = $this->prefix.':'.$roomId;
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
    public function deleteState(string $roomId): void
    {
        $key = $this->prefix.':'.$roomId;
        try {
            Redis::del($key);
        } catch (Exception $e) {
            Log::error("Redis deleteState error for key {$key}: ".$e->getMessage());
            throw $e;
        }

    }
}
