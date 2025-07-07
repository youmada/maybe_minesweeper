<?php

namespace App\Routes;

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('room.{roomId}', function ($player, $roomId) {
    return [
        'sessionId' => $player->session_id,
    ];
}, ['guards' => 'magicLink']);
