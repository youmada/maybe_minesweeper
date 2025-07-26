<?php

namespace App\Events;

use App\Models\Room;
use App\Models\RoomState;
use Carbon\Carbon;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoomPlayerList implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(protected Room $room) {}

    public function broadcastOn(): array
    {
        return [
            new PresenceChannel("room.{$this->room->public_id}"),
        ];
    }

    public function broadcastWith(): array
    {

        $players = $this->room->players()->withPivot(['joined_at'])->get();

        return [
            'players' => $players->map(function ($player) {
                $currentPlayer = RoomState::where('room_id', $this->room->id)->first()->current_player;

                return [
                    'id' => $player->public_id,
                    'joinedAt' => Carbon::parse($player->joined_at)->toISOString(),
                    'isCurrentTurn' => $currentPlayer === $player->public_id,
                ];
            }),
        ];
    }
}
