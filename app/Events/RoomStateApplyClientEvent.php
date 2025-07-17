<?php

namespace App\Events;

use App\Models\Room;
use App\Repositories\Composites\RoomCompositeRepository;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoomStateApplyClientEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(protected Room $room) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("room.{$this->room->public_id}.state"),
        ];
    }

    public function broadcastWith(): array
    {
        $roomRepository = app(RoomCompositeRepository::class)->get($this->room->id);
        $turnStates = $roomRepository->getActionState();

        return [
            'data' => [
                'currentPlayer' => $roomRepository->getCurrentOrder(),
                'turnActionState' => [
                    'tileOpened' => $turnStates['tileOpened'],
                    'flagCount' => $turnStates['flagCount'],
                    'flagLimit' => $roomRepository->getFlagLimit(),
                ],
            ],
        ];
    }
}
