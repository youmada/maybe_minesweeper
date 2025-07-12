<?php

namespace App\Repositories\Interfaces;

use App\Domain\Room\RoomAggregate;
use App\Domain\Room\RoomState;

interface RoomCompositesRepositoryInterface
{
    public function create(RoomAggregate $roomAggregate): string;

    public function get(string $roomId): RoomAggregate|RoomState|null;

    public function update(RoomAggregate $roomAggregate, string $roomId): void;

    public function delete(string $roomId): void;
}
