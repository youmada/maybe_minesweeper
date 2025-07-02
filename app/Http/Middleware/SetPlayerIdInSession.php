<?php

namespace App\Http\Middleware;

use App\Utils\UUIDFactory;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetPlayerIdInSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->has('player_id')) {
            $request->session()->put('player_id', UUIDFactory::generate());
        }

        return $next($request);
    }
}
