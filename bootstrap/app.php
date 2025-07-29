<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
            \App\Http\Middleware\SetPlayerIdInSession::class,
        ]);
        $middleware->alias([
            'room.auth' => \App\Http\Middleware\RoomAuthMiddleware::class,
        ]);
        $middleware->redirectGuestsTo(function (Request $request) {
            // JSONリクエストやInertiaリクエストなら401を返す
            if ($request->expectsJson() || $request->header('X-Inertia')) {
                abort(401, '認証に失敗しました');
            }

            return route('Home');
        });

        //
    })
    ->withExceptions(function (Exceptions $exceptions) {})->create();
