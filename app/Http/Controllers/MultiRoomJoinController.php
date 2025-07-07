<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Services\Multi\JoinRoomService;
use App\Services\Multi\MagicLinkService;
use DB;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MultiRoomJoinController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Room $room, JoinRoomService $JoinRoomService)
    {
        $magicLinkToken = $request->query('token');
        $playerId = $request->session()->get('player_id');

        $isValid = (new MagicLinkService)->verify($room->id, $magicLinkToken, $playerId);

        if (! $isValid) {
            abort(401);
        }
        // 参加したユーザIDを保存
        DB::transaction(function () use ($JoinRoomService, $room, $playerId) {
            if (! in_array($playerId, $room->players->toArray(), true)) {
                $JoinRoomService($room->id, $playerId);
            }
        });

        return Inertia::location(route('multi.rooms.play.show', ['room' => $room]));
    }
}
