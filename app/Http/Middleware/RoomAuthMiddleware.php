<?php

namespace App\Http\Middleware;

use App\Models\Room;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoomAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $roomId = $request->route('room');
        $playerId = $request->session()->get('player_id', '');

        $isJoined = Room::where('id', $roomId)
            ->whereJsonContains('players', $playerId)
            ->exists();

        if (! $isJoined) {
            return response()->json([
                'message' => 'このルームに参加していません',
            ], 403);
        }

        return $next($request);
    }
}
