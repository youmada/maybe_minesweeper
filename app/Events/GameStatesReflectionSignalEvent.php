<?php

namespace App\Events;

use App\Models\Room;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GameStatesReflectionSignalEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(protected Room $room) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("game.{$this->room->public_id}"),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'signal' => true,
        ];
    }
}
