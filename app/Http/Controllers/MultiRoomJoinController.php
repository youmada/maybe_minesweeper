<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Models\Room;
use App\Services\Multi\JoinRoomService;
use App\Services\Multi\MagicLinkService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class MultiRoomJoinController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Room $room, JoinRoomService $JoinRoomService)
    {
        $magicLinkToken = $request->query('token');
        $playerId = Player::getPlayerIdentifier();

        $isValid = (new MagicLinkService)->verify($room->id, $magicLinkToken, $playerId);

        if (! $isValid) {
            abort(401);
        }
        // 参加したユーザIDを保存

        if (! $room->players->contains($playerId)) {
            $JoinRoomService($room->id, $playerId);
        }

        $player = Player::where('public_id', $playerId)->first();

        Auth::guard('magicLink')->login($player);

        return Inertia::location(route('multi.rooms.play.show', ['room' => $room]));
    }
}
