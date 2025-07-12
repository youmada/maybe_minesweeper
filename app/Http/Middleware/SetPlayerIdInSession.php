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
        if (! $request->session()->has('public_id')) {
            $request->session()->put('public_id', UUIDFactory::random());
        }

        return $next($request);
    }
}
