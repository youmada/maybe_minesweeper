<?php

namespace App\Routes;

use App\Models\Player;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('room.{roomId}', function (Player $player, $roomId) {
    return [
        'sessionId' => $player->session_id,
    ];
}, ['guards' => 'magicLink']);
