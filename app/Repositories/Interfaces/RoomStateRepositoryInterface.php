<?php

namespace App\Repositories\Interfaces;

use App\Domain\Room\RoomState;

interface RoomStateRepositoryInterface
{
    public function save(RoomState $roomState, string $roomId): void;

    public function get(string $roomId): ?RoomState;

    public function update(RoomState $roomState, string $roomId): void;

    public function delete(string $roomId): void;
}
