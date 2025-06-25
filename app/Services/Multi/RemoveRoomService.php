<?php

namespace App\Services\Multi;

use App\Repositories\Interfaces\RoomCompositesRepositoryInterface;

class RemoveRoomService
{
    public function __construct(protected RoomCompositesRepositoryInterface $roomRepository) {}

    public function __invoke(string $roomId): void
    {
        $this->roomRepository->delete($roomId);
    }
}
