<?php

namespace App\Repositories\Interfaces;

use App\Domain\Room\RoomAggregate;

interface RoomStateRepositoryInterface
{
    public function save(RoomAggregate $roomAggregate, string $roomId): void;

    public function get(string $roomId): ?RoomAggregate;

    public function update(RoomAggregate $roomAggregate, string $roomId): void;

    public function delete(string $roomId): void;
}
