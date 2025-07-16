<?php

namespace App\Factories;

use App\Domain\Room\Room;
use App\Domain\Room\RoomAggregate;

class RoomAggregateFactory
{
    public static function create(string $roomName, int $maxPlayers, string $ownerId, string $expireAt, bool $isPrivate = true, array $players = [], int $flagLimit = 5): RoomAggregate
    {
        $room = new Room($roomName, $maxPlayers, $players, $expireAt, $isPrivate, $ownerId);
        $roomState = RoomStateFactory::createNew([], $flagLimit);

        return new RoomAggregate($room, $roomState);
    }

    public static function createFromRedis(array $data): RoomAggregate
    {
        $roomData = $data['room'];
        $roomStateData = $data['roomState'];
        $room = new Room($roomData['name'], $roomData['maxPlayer'], $roomData['players'], $roomData['expireAt'], $roomData['isPrivate'], $roomData['ownerId']);
        $roomState = RoomStateFactory::createFromRedis($roomStateData);

        return new RoomAggregate($room, $roomState);
    }
}
