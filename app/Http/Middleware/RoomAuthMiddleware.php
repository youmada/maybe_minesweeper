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
        $roomId = $request->route('room')->id;
        $playerId = $request->session()->get('player_id', '');
        try {
            $isJoined = Room::whereJsonContains('players', $playerId)
                ->where('id', $roomId)
                ->where('expire_at', '>', now())
                ->exists();
        } catch (\Exception $e) {
            $isJoined = false;
        }

        if (! $isJoined) {
            abort(401);
        }

        return $next($request);
    }
}
