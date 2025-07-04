<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Services\Multi\MagicLinkService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MultiRoomJoinController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Room $room)
    {
        $magicLinkToken = $request->query('token');
        $playerId = $request->session()->get('player_id');

        $isValid = (new MagicLinkService)->verify($room->id, $magicLinkToken, $playerId);

        if (! $isValid) {
            abort(401);
        }
        // 参加したユーザIDを保存
        if (! in_array($playerId, $room->players, true)) {
            $players = $room->players ?? [];
            $players[] = $playerId;
            $room->players = $players;
            $room->save();
        }

        return Inertia::location(route('multi.rooms.play.show'));
    }
}
