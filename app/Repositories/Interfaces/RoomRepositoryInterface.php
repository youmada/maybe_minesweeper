<?php

namespace App\Repositories\Interfaces;

use stdClass;

interface RoomRepository
{
    public function saveState(string $userId, string $roomId): void;

    public function getState(string $roomId): ?stdClass;

    public function updateState(string $userId, string $roomId): void;

    public function deleteState(string $roomId): void;
}
