<?php

use App\Models\Player;
use App\Models\Room;
use App\Utils\UUIDFactory;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->playerId = Str::random(40);
    $this->player = Player::factory()->create([
        'session_id' => $this->playerId,
    ]);
});

it("should response session user's room data", function () {
    $this->room = Room::factory()->create([
        'magic_link_token' => UUIDFactory::generate(),
        'owner_id' => $this->player->id,
    ]);
    $response = $this->withSession(['player_id' => $this->playerId])->get('/multi/rooms');
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Multi/Rooms')
        ->has('data', 1)
        ->has(
            'data.0',
            fn (Assert $page) => $page
                ->where('name', $this->room->name)
                ->where('expireAt', $this->room->expire_at->toDateTimeString())
                ->where('magicLink', config('app.url')."/multi/rooms/{$this->room->public_id}/join?token={$this->room->magic_link_token}")
        )
    );
});

it("should create a room from user's request data.", function () {

    $this->assertDatabaseCount('rooms', 0);
    $this->assertDatabaseCount('room_states', 0);
    $this->assertDatabaseCount('players', 1);
    $this->assertDatabaseCount('game_states', 0);
    $response = $this->withSession(['player_id' => $this->playerId])->post('/multi/rooms', [
        'name' => 'TestRoom',
        'boardWidth' => 10,
        'boardHeight' => 10,
        'mineRatio' => 20,
        'maxPlayer' => 4,
        'expireAt' => 7,
    ]);
    $roomId = Room::where('owner_id', $this->player->id)->first()->id;

    $room = Room::find($roomId);
    $player = $room->players()->where('session_id', $this->playerId)->first();
    $this->assertDatabaseCount('rooms', 1);
    $this->assertDatabaseHas('rooms', [
        'name' => 'TestRoom',
        'max_player' => 4,
        'owner_id' => $this->player->id,
        'expire_at' => Carbon::now()->addDays(7)->toDateString(),
    ]);

    $this->assertDatabaseCount('room_states', 1);
    $this->assertDatabaseHas('room_states', [
        'room_id' => $roomId,
        'turn_order' => json_encode([]),
        'current_player' => $player->id,
        'status' => 'waiting',
        'flag_limit' => 5,
    ]);

    $this->assertDatabaseCount('room_player', 1);
    $this->assertDatabaseHas('room_player', [
        'room_id' => $roomId,
        'player_id' => $this->player->id,
    ]);

    $this->assertDatabaseCount('game_states', 1);
    $this->assertDatabaseHas('game_states', [
        'room_id' => $roomId,
        'width' => 10,
        'height' => 10,
        'num_of_mines' => 20,
        'is_game_started' => false,
        'is_game_clear' => false,
        'is_game_over' => false,
    ]);

    $response->assertRedirect('/multi/rooms/'.$room->public_id.'/join'.'?token='.$room->magic_link_token);
});
