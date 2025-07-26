<?php

namespace App\Events;

use App\Models\Room;
use App\Repositories\Composites\GameCompositeRepository;
use App\Repositories\Composites\RoomCompositeRepository;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoomStatusApplyClient implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(protected Room $room)
    {
        //
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("room.{$this->room->public_id}.data"),
        ];
    }

    public function broadcastWith(): array
    {

        $roomState = app(RoomCompositeRepository::class)->get($this->room->id);
        $gameState = app(GameCompositeRepository::class)->getState($this->room->id);

        return [
            'room' => [
                'status' => $roomState->getRoomStatus(),
            ],
            'game' => [
                'status' => $gameState->getGameStatus(),
            ],
        ];
    }
}
