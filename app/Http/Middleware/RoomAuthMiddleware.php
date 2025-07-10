<?php

namespace App\Http\Middleware;

use App\Models\Room;
use Closure;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class RoomAuthMiddleware extends Authenticate
{
    protected function redirectTo($request): string|RedirectResponse|null
    {
        return redirect()->route('Home');
    }

    /**
     * Handle an incoming request.
     * セッションにあるplayerIdがroom_playerテーブルに特録されている。かつ
     * ルーム有効期限以内かつ
     * ルームが存在している
     */
    public function handle($request, Closure $next, ...$guards): Response
    {
        $roomId = $request->route('room');
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
