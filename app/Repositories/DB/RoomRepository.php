<?php

namespace App\Repositories\DB;

use App\Repositories\Interfaces\RoomRepositoryInterface;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use stdClass;

class RoomRepository implements RoomRepositoryInterface
{
    private string $prefix = 'minesweeper:room';

    /**
     * @throws Exception
     */
    public function saveState(string $userId, string $roomId): void
    {
        $key = $this->prefix.':'.$roomId;
        $value = $userId;
        try {
            Redis::set($key, $value);
        } catch (Exception $e) {
            Log::error("Redis saveState error for key {$key}: ".$e->getMessage());
            throw $e;
        }
    }

    public function getState(string $roomId): ?stdClass
    {
        $key = $this->prefix.':'.$roomId;

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
    public function updateState(string $userId, string $roomId): void
    {
        $key = $this->prefix.':'.$roomId;
        $value = $userId;
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
