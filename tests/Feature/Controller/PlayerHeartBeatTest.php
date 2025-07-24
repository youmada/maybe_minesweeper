<?php

use App\Models\Player;
use App\Models\Room;
use Carbon\Carbon;

beforeEach(function () {
    $this->freezeTime();

    $this->player = Player::factory()->create();
    $this->room = Room::factory()->create();
    $this->room->players()->attach($this->player->id);
});

it('should update player model column last_exists_at', function () {

    // 事前チェック
    $this->assertDatabaseHas('room_player', [
        'player_id' => $this->player->id,
        'last_exists_at' => null,
    ]);
    $this->travel(10)->seconds();
    // 実行
    $response = $this->withSession(['public_id' => $this->player->public_id])
        ->actingAs($this->player, 'magicLink')
        ->putJson("/multi/rooms/{$this->room->public_id}/play/heartbeat", [
            'player_id' => $this->player->public_id,
        ]);

    $response->assertStatus(201);

    $this->assertDatabaseHas('room_player', [
        'player_id' => $this->player->id,
        'last_exists_at' => Carbon::now(),
    ]);
});

it('should 422 response, when invalid player id', function () {
    $response = $this->withSession(['public_id' => $this->player->public_id])
        ->actingAs($this->player, 'magicLink')
        ->putJson("/multi/rooms/{$this->room->public_id}/play/heartbeat", [
            'player_id' => 'invalid_player_id',
        ]);

    $response->assertStatus(422);
});
