<?php

namespace App\Services\Multi;

use App\Factories\RoomAggregateFactory;
use App\Repositories\Interfaces\RoomCompositesRepositoryInterface;

class CreateRoomService
{
    public function __construct(protected RoomCompositesRepositoryInterface $roomRepository, protected RoomAggregateFactory $roomFactory) {}

    /**
     * @return string roomId
     */
    public function __invoke(string $roomName, int $maxPlayers, $ownerId, string $expireAt, $isPrivate, $players, $flagLimit): string
    {
        $roomAggregate = $this->roomFactory->create($roomName, $maxPlayers, $ownerId, $expireAt, $isPrivate, $players, $flagLimit);

        // ルームを作成する。
        return $this->roomRepository->create($roomAggregate);
    }
}
