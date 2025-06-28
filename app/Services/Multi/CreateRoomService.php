<?php

namespace App\Services\Multi;

use App\Factories\RoomAggregateFactory;
use App\Repositories\Interfaces\RoomCompositesRepositoryInterface;

class CreateRoomService
{
    public function __construct(protected RoomCompositesRepositoryInterface $roomRepository, protected RoomAggregateFactory $roomFactory) {}

    public function __invoke(string $roomId, string $roomName, int $maxPlayers, $ownerId, $isPrivate, $players, $flagLimit): void
    {
        $roomAggregate = $this->roomFactory->create($roomName, $maxPlayers, $ownerId, $isPrivate, $players, $flagLimit);
        // ルームを作成する。
        $this->roomRepository->save($roomAggregate, $roomId);
    }
}
