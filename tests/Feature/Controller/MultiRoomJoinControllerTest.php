<?php

use App\Models\Room;
use App\Utils\UUIDFactory;

beforeEach(function () {
    $this->playerId = UUIDFactory::generate();
    $this->playerId2 = UUIDFactory::generate();
    $this->room = Room::factory()->create([
        'owner_id' => $this->playerId,
        'players' => [],
    ]);
});

it('can join a room. because of magic link check passed', function () {
    $response = $this->withSession(['player_id' => $this->playerId])
        ->get("/multi/rooms/{$this->room->public_id}/join?token={$this->room->magic_link_token}");
    $response->assertRedirect('/multi/rooms/'.$this->room->public_id.'/play');
});

it('can push a player to player list. when player join a room.', function () {
    $this->assertDatabaseHas('rooms', [
        'id' => $this->room->id,
        'players' => json_encode([]),
    ]);
    $response = $this->withSession(['player_id' => $this->playerId])
        ->get("/multi/rooms/{$this->room->public_id}/join?token={$this->room->magic_link_token}");
    $response->assertRedirect('/multi/rooms/'.$this->room->public_id.'/play');

    $this->assertDatabaseHas('rooms', [
        'id' => $this->room->id,
        'players' => json_encode([$this->playerId]),
    ]);
});

it('can not join a room. because of magic link check failed', function () {
    $response = $this->withSession(['player_id' => $this->playerId])
        ->get("/multi/rooms/{$this->room->public_id}/join?token=invalid_token");
    $response->assertStatus(401);
});

it('can not join a room. because of room is not exists.', function () {
    $response = $this->withSession(['player_id' => $this->playerId])
        ->get("/multi/rooms/not-exists/join?token={$this->room->magic_link_token}");
    $response->assertStatus(404);
});

it('can join a room. because of player is already registered.', function () {
    $this->room->update([
        'players' => [$this->playerId],
    ]);
    $this->room->refresh();
    $response = $this->withSession(['player_id' => $this->playerId])
        ->get("/multi/rooms/{$this->room->public_id}/join?token={$this->room->magic_link_token}");
    $response->assertRedirect('/multi/rooms/'.$this->room->public_id.'/play');
});

it('can not join a room. because of player limit is over.', function () {
    $this->room->update([
        'max_player' => 1,
        'players' => [$this->playerId2],
    ]);
    $this->room->refresh();
    $this->withSession(['player_id' => UUIDFactory::generate()])
        ->get("/multi/rooms/{$this->room->public_id}/join?token={$this->room->magic_link_token}")
        ->assertStatus(401);
});

it('can join a room. because of player limit is over but player is already registered.', function () {
    $this->room->update([
        'max_player' => 1,
        'players' => [$this->playerId],
    ]);
    $this->room->refresh();
    $this->withSession(['player_id' => $this->playerId])
        ->get("/multi/rooms/{$this->room->public_id}/join?token={$this->room->magic_link_token}")
        ->assertRedirect('/multi/rooms/'.$this->room->public_id.'/play');
});

it('can skip to set a player id in players column. when already set a player id in player column', function () {
    $this->room->update([
        'players' => [$this->playerId],
        'max_player' => 2,
    ]);
    $this->room->refresh();
    $this->withSession(['player_id' => $this->playerId])
        ->get("/multi/rooms/{$this->room->public_id}/join?token={$this->room->magic_link_token}")
        ->assertRedirect('/multi/rooms/'.$this->room->public_id.'/play');
    $this->assertDatabaseHas('rooms', [
        'id' => $this->room->id,
        'players' => json_encode([$this->playerId]),
    ]);
});
