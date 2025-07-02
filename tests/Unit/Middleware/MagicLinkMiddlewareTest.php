<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\MagicLinkMiddleware;
use App\Models\Room;
use App\Utils\UUIDFactory;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    Route::get('/magic-link/{room}', fn () => response(['ok' => true]))
        ->middleware(['web', MagicLinkMiddleware::class]);

    $this->room = Room::factory()->create([
        'magic_link_token' => UUIDFactory::generate(),
        'public_id' => UUIDFactory::generate(),
    ]);
});

it('can access magic link route', function () {
    $response = $this->get('/magic-link/'.$this->room->public_id.'?token='.$this->room->magic_link_token);
    $response->assertOk();
});

it('can not access magic link route. because of invalid token', function () {
    $response = $this->get('/magic-link/'.$this->room->id.'?token=invalid_token');
    $response->assertStatus(400);
});

it('can not access magic link route. because of room is not exists.', function () {
    $response = $this->get('/magic-link/not-exists?token='.$this->room->magic_link_token);
    $response->assertStatus(400);
});
