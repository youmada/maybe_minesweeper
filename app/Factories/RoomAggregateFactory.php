<?php

namespace App\Factories;

use App\Domain\Room\Room;
use App\Domain\Room\RoomAggregate;

class RoomAggregateFactory
{
    public static function create(string $roomName, int $maxPlayers, string $ownerId, bool $isPrivate = true, array $players = [], int $flagLimit = 5): RoomAggregate
    {
        $room = new Room($roomName, $maxPlayers, $players, $isPrivate, $ownerId);
        $roomState = RoomStateFactory::createNew([], $flagLimit);

        return new RoomAggregate($room, $roomState);
    }
}
