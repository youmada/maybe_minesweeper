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
     * セッションにあるplayerIdがroom_playerテーブルに特録されている。かつ
     * ルーム有効期限以内かつ
     * ルームが存在している
     *
     * @param  \Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $roomId = $request->route('room')->public_id;
        $sessionId = $request->session()->get('player_id', '');
        try {
            $canJoin = Room::canJoin($roomId, $sessionId);
        } catch (\Exception $e) {
            $canJoin = false;
        }

        if (! $canJoin) {
            abort(401);
        }

        return $next($request);
    }
}
