<?php

namespace App\Routes;

use App\Models\Player;
use App\Models\Room;
use App\Models\RoomState;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('room.{publicId}', function (Player $player, $publicId) {

    $roomId = Room::findByPublicId($publicId)->first()->id;
    $currentPlayer = RoomState::where('room_id', $roomId)->first()->current_player;
    $isAuth = $player
        ->rooms()
        ->findByPublicId($publicId)
        ->exists();

    if (! $isAuth) {
        return false;
    }

    $joinedAt = $player->rooms()->where('rooms.id', $roomId)->first()->pivot->joined_at;

    return ['id' => $player->public_id, 'joined_at' => $joinedAt, 'isCurrentTurn' => $currentPlayer === $player->public_id];

});

Broadcast::channel('room.{publicId}.data', function (Player $player, $publicId) {
    return $player
        ->rooms()
        ->findByPublicId($publicId)
        ->exists();

}, ['guards' => ['magicLink']]);
