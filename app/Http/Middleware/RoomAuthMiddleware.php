<?php

namespace App\Http\Middleware;

use App\Models\Player;
use App\Models\Room;
use Closure;
use Illuminate\Auth\Middleware\Authenticate;
use Symfony\Component\HttpFoundation\Response;

class RoomAuthMiddleware extends Authenticate
{
    /**
     * Handle an incoming request.
     * セッションにあるplayerIdがroom_playerテーブルに特録されている。かつ
     * ルーム有効期限以内かつ
     * ルームが存在している
     */
    public function handle($request, Closure $next, ...$guards): Response
    {
        $roomId = $request->route('room');
        $sessionId = Player::getPlayerIdentifier();
        $canJoin = Room::canJoin($roomId, $sessionId);

        if (! $canJoin) {
            abort(403, 'このルームには参加することができません');
        }

        return $next($request);
    }
}
