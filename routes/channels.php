<?php

namespace App\Routes;

use App\Models\Player;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('room.{publicId}', function (Player $player, $publicId) {
    $isAuth = $player
        ->rooms()
        ->findByPublicId($publicId)
        ->exists();

    if (! $isAuth) {
        return false;
    }

    return ['sessionId' => $player->session_id];

}, ['guards' => ['magicLink']]);
