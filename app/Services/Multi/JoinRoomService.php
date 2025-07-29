<?php

namespace App\Services\Multi;

use App\Repositories\Interfaces\RoomCompositesRepositoryInterface;

class JoinRoomService
{
    public function __construct(protected RoomCompositesRepositoryInterface $roomRepository) {}

    public function __invoke(string $roomId, string $playerId): void
    {
        // 現在のルームを取得
        $currentRoom = $this->roomRepository->get($roomId);

        // 現在のルームにユーザを追加する
        $currentRoom->join($playerId);
        $this->roomRepository->update($currentRoom, $roomId);
    }
}
