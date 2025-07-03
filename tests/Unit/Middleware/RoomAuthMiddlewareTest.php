<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\RoomAuthMiddleware;
use App\Models\Room;
use App\Utils\UUIDFactory;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->playerId = Str::random(40);

    Route::any('/magic-link/{room}/play', fn (Room $room) => response(['ok' => true]))
        ->whereUuid('room')
        ->middleware(['web', RoomAuthMiddleware::class]);

    $this->room = Room::factory()->create([
        'magic_link_token' => Str::random(32),
        'players' => [$this->playerId],
        'public_id' => UUIDFactory::generate(),
    ]);
});

it('can access magic link route', function () {
    $response = $this
        ->withSession(['player_id' => $this->playerId])
        ->get('/magic-link/'.$this->room->public_id.'/play');
    $response->assertOk();
});

it('can not access magic link route. because of invalid player id', function () {
    $response = $this
        ->withSession(['player_id' => 'invalid_player_id'])
        ->get('/magic-link/'.$this->room->public_id.'/play');
    $response->assertStatus(401);
});

it('can not access magic link route. because of room is not exists.', function () {
    $response = $this
        ->withSession(['player_id' => $this->playerId])
        ->get('/magic-link/not-exists/play');
    $response->assertStatus(404);
});

it("can not access magic link route. because of room' expire is over", function () {
    $this->room->update([
        'expire_at' => now()->subDay(),
    ]);
    $this->room->refresh();
    $response = $this
        ->withSession(['player_id' => $this->playerId])
        ->get('/magic-link/'.$this->room->public_id.'/play');
    $response->assertStatus(401);
});
