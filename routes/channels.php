<?php

namespace App\Routes;

use App\Models\Player;
use App\Models\Room;
use App\Models\RoomState;
use Carbon\Carbon;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('room.{publicId}', function (Player $player, $publicId) {

    $roomId = Room::findByPublicId($publicId)->first()->id;
    $currentPlayer = RoomState::where('room_id', $roomId)->first()->current_player;
    $isAuth = isRoomExists($player, $publicId);

    if (! $isAuth) {
        return false;
    }

    $joinedAt = $player->rooms()->where('rooms.id', $roomId)->first()->pivot->joined_at;

    return ['id' => $player->public_id, 'joinedAt' => Carbon::parse($joinedAt)->toISOString(), 'isCurrentTurn' => $currentPlayer === $player->public_id];

});

Broadcast::channel('room.{publicId}.data', function (Player $player, $publicId) {
    return isRoomExists($player, $publicId);

}, ['guards' => ['magicLink']]);

Broadcast::channel('game.{publicId}', function (Player $player, $publicId) {
    return isRoomExists($player, $publicId);
});

if (! function_exists(__NAMESPACE__.'\isRoomExists')) {
    function isRoomExists(Player $player, $publicId): bool
    {
        return $player
            ->rooms()
            ->findByPublicId($publicId)
            ->exists();
    }
}
