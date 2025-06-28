<?php

namespace App\Services\Multi;

use App\Domain\Minesweeper\TileActionMode;
use App\Domain\Room\RoomAggregate;
use App\Repositories\Interfaces\RoomCompositesRepositoryInterface;

class AdvanceTurnService
{
    public function __construct(protected RoomCompositesRepositoryInterface $roomRepository, protected RoomAggregate $roomFactory) {}

    public function __invoke(string $roomId, string $userId, TileActionMode $actionMode): void
    {
        // 現在のルームを取得
        $currentRoom = $this->roomRepository->get($roomId);

        $currentRoom->operate($userId, $actionMode);
        $this->roomRepository->update($currentRoom, $roomId);
    }
}
