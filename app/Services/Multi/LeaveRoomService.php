<?php

namespace App\Services\Multi;

use App\Domain\Room\RoomAggregate;
use App\Repositories\Interfaces\RoomCompositesRepositoryInterface;

class LeaveRoomService
{
    public function __construct(protected RoomCompositesRepositoryInterface $roomRepository) {}

    public function __invoke(string $roomId, string $playerId): void
    {
        // 現在のルームを取得
        $currentRoom = $this->roomRepository->get($roomId);
        // 現在のプレイヤー一覧から指定ユーザーを退出させる
        if ($currentRoom instanceof RoomAggregate) {
            $currentRoom->leave($playerId);
            $this->roomRepository->update($currentRoom, $roomId);
        }
    }
}
