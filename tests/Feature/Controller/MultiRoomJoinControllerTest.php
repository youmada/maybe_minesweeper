<?php

use App\Models\Player;
use App\Models\Room;
use App\Models\RoomState;
use App\Utils\UUIDFactory;

beforeEach(function () {
    $this->player1 = Player::factory()->create([]);
    $this->player2 = PLayer::factory()->create([]);
    $this->room = Room::factory()->create([
        'owner_id' => $this->player1->id,
        'max_player' => 2,
    ]);
    RoomState::factory()->recycle($this->room)->create();
});

it('can join a room', function () {
    $response = $this->withSession(['public_id' => $this->player1->public_id])
        ->get("/multi/rooms/{$this->room->public_id}/join?token={$this->room->magic_link_token}");
    $response->assertRedirect('/multi/rooms/'.$this->room->public_id.'/play');
});

it('can push a player to player list. when player join a room.', function () {
    $this->assertDatabaseHas('rooms', [
        'id' => $this->room->id,
    ]);
    $response = $this->withSession(['public_id' => $this->player1->public_id])
        ->get("/multi/rooms/{$this->room->public_id}/join?token={$this->room->magic_link_token}");
    $response->assertRedirect('/multi/rooms/'.$this->room->public_id.'/play');

    $this->assertDatabaseHas('rooms', [
        'id' => $this->room->id,
    ]);
    $this->assertDatabaseHas('room_player', [
        'room_id' => $this->room->id,
        'player_id' => $this->player1->id,
    ]);
    $this->assertDatabaseHas('players', [
        'id' => $this->player1->id,
        'public_id' => $this->player1->public_id,
    ]);
});

it('can not join a room. because of magic link check failed', function () {
    $response = $this->withSession(['public_id' => $this->player1->public_id])
        ->get("/multi/rooms/{$this->room->public_id}/join?token=invalid_token");
    $response->assertStatus(401);
});

it('can not join a room. because of room is not exists.', function () {
    $response = $this->withSession(['public_id' => $this->player1->public_id])
        ->get("/multi/rooms/not-exists/join?token={$this->room->magic_link_token}");
    $response->assertStatus(404);
});

it('can join a room. because of player is already registered.', function () {
    $this->room->players()->attach($this->player1->id, [
        'joined_at' => now(),
        'left_at' => null,
    ]);
    $this->room->refresh();
    $response = $this->withSession(['public_id' => $this->player1->public_id])
        ->get("/multi/rooms/{$this->room->public_id}/join?token={$this->room->magic_link_token}");
    $response->assertRedirect('/multi/rooms/'.$this->room->public_id.'/play');
});

it('can not join a room. because of player limit is over.', function () {
    $this->room->update([
        'max_player' => 1,
    ]);
    $this->room->players()->attach($this->player1->id, [
        'joined_at' => now(),
        'left_at' => null,
    ]);
    $this->room->refresh();
    $this->withSession(['public_id' => UUIDFactory::generate()])
        ->get("/multi/rooms/{$this->room->public_id}/join?token={$this->room->magic_link_token}")
        ->assertStatus(401);
});

it('can join a room. because of player limit is over but player is already registered.', function () {
    $this->room->update([
        'max_player' => 1,
    ]);
    $this->withSession(['public_id' => $this->player1->public_id])
        ->get("/multi/rooms/{$this->room->public_id}/join?token={$this->room->magic_link_token}")
        ->assertRedirect('/multi/rooms/'.$this->room->public_id.'/play');
});

it('can skip to set a player id in players column. when already set a player id in player column', function () {
    $this->room->update([
        'max_player' => 1,
    ]);
    $this->room->refresh();
    $this->withSession(['public_id' => $this->player1->public_id])
        ->get("/multi/rooms/{$this->room->public_id}/join?token={$this->room->magic_link_token}")
        ->assertRedirect('/multi/rooms/'.$this->room->public_id.'/play');
    $this->assertDatabaseHas('room_player', [
        'room_id' => $this->room->id,
        'player_id' => $this->player1->id,
    ]);
    $this->assertDatabaseHas('players', [
        'id' => $this->player1->id,
        'public_id' => $this->player1->public_id,
    ]);
});
