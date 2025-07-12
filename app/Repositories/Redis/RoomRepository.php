<?php

namespace App\Repositories\Redis;

use App\Domain\Room\RoomState;
use App\Repositories\Interfaces\RoomStateRepositoryInterface;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class RoomRepository implements RoomStateRepositoryInterface
{
    private string $prefix = 'minesweeper:room';

    /**
     * @throws Exception
     */
    public function save(RoomState $roomState, string $roomId): void
    {
        $key = $this->prefix.':'.$roomId;
        $value = json_encode($roomState->toArray());
        try {
            Redis::set($key, $value);
        } catch (Exception $e) {
            Log::error("Redis saveState error for key {$key}: ".$e->getMessage());
            throw $e;
        }
    }

    public function get(string $roomId): ?RoomState
    {
        $key = $this->prefix.':'.$roomId;

        try {
            $value = Redis::get($key);
            $roomState = RoomState::fromArray(json_decode($value, true));
        } catch (Exception $e) {
            Log::error("Redis getState error for key {$key}: ".$e->getMessage());

            return null;
        }

        return $roomState;
    }

    /**
     * @throws Exception
     */
    public function update(RoomState $roomState, string $roomId): void
    {
        $key = $this->prefix.':'.$roomId;
        $value = json_encode($roomState->toArray());
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
    public function delete(string $roomId): void
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
