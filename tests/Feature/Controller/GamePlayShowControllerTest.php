<?php

use App\Models\Player;
use App\Models\Room;
use App\Models\RoomState;
use App\Utils\UUIDFactory;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->player = Player::factory()->create();
    $this->publicId = UUIDFactory::generate();
    $this->room = Room::factory()->create([
        'public_id' => $this->publicId,
    ]
    );
    $this->roomState = RoomState::factory()->create([
        'room_id' => $this->room->id,
        'current_player' => $this->player->id,
    ]);
    $this->room->players()->attach($this->player->id, [
        'joined_at' => now(),
        'left_at' => null,
    ]);
});

it('should response a room data by inertia.', function () {
    $response = $this->actingAs($this->player, 'magicLink')
        ->withSession(['player_id' => $this->player->session_id])
        ->get("multi/rooms/{$this->room->public_id}/play");

    $response->assertInertia(fn (Assert $inertia) => $inertia
        ->component('Multi/Play')
        ->has('data', fn (Assert $page) => $page
            ->has('room', fn (Assert $page) => $page
                ->where('publicId', $this->room->public_id)
                ->where('name', $this->room->name)
                ->where('maxPlayer', $this->room->max_player)
                ->where('ownerId', $this->room->owner_id)
                ->where('magicLink', config('app.url')."/multi/rooms/{$this->room->public_id}/join?token={$this->room->magic_link_token}")
                ->where('status', RoomState::where('room_id', $this->room->id)->first()->status)
                ->where('turnOrder', RoomState::where('room_id', $this->room->id)->first()->turn_order)
                ->where('currentPlayer', $this->player->id)
            )
            ->has('game', fn (Assert $page) => $page
                ->hasAll(['width', 'height', 'numOfMines', 'tileStates', 'isGameStarted', 'isGameOver', 'isGameClear', 'visitedTiles'])
            )
        )
    );
});
