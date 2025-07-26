<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Models\Room;
use Illuminate\Http\Request;

class PlayerHeartBeatController extends Controller
{
    public function __invoke(Request $request, Room $room)
    {
        $attributes = $request->validate([
            'player_id' => ['required', 'string', 'exists:players,public_id'],
        ], [
            'player_id.exists' => 'プレイヤーは存在していません',
        ]);

        $player = Player::where('public_id', $attributes['player_id'])->first();

        $room->players()->updateExistingPivot($player->id, ['last_exists_at' => now()]);

        return response()->json(['message' => 'ok'], 201);
    }
}
