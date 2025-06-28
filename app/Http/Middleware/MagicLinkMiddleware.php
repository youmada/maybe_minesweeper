<?php

namespace App\Http\Middleware;

use App\Services\Multi\MagicLinkService;
use App\Utils\UUIDFactory;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MagicLinkMiddleware
{
    public function __construct(protected MagicLinkService $magicLinkService) {}

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(Request): (Response)  $next
     *
     * @throws Exception
     */
    public function handle(Request $request, Closure $next): Response
    {

        $roomId = $request->route('room');
        $magicLinkToken = $request->query('token');
        $playerId = $request->session()->get('player_id', '');

        if (! $playerId) {
            $request->session()->put('player_id', $playerId = UUIDFactory::generate());
        }

        $isValid = $this->magicLinkService->verify($roomId, $magicLinkToken, $playerId);
        if (! $isValid) {
            return response()->json(['message' => 'ルームに参加できませんでした'], 400);
        }

        return $next($request);
    }
}
