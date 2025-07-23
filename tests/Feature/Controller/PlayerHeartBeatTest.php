<?php

use App\Models\Player;
use App\Models\Room;
use App\Models\RoomState;
use App\Services\Minesweeper\MinesweeperService;
use App\Services\Multi\CreateRoomService;
use App\Services\Multi\JoinRoomService;
use Carbon\Carbon;

beforeEach(function () {
    $this->freezeTime();
    $this->width = 5;
    $this->height = 5;
    $this->numOfMines = 5;
    $this->player = Player::factory()->create();

    $roomId = app(CreateRoomService::class)(
        roomName: 'test room',
        maxPlayers: 2,
        ownerId: $this->player->public_id,
        expireAt: Carbon::now()->addDay(),
        isPrivate: true,
        players: [$this->player->public_id],
        flagLimit: 5);
    $this->room = Room::find($roomId);
    $this->roomState = RoomState::where('room_id', $this->room->id)->first();
    app(JoinRoomService::class)($this->room->id, $this->player->public_id);
    app(MinesweeperService::class)->initializeGame($this->room->id, $this->width, $this->height, $this->numOfMines);
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
