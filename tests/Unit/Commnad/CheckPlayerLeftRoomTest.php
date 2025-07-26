<?php

use App\Models\Player;
use App\Models\Room;
use App\Repositories\Composites\RoomCompositeRepository;
use App\Services\Multi\CreateRoomService;
use App\Services\Multi\JoinRoomService;

beforeEach(function () {
    $this->freezeTime();

    $this->player = Player::factory()->create();
    // ルーム作成
    $roomID = app(CreateRoomService::class)(
        'room name',
        3,
        $this->player->public_id,
        now()->addDay(),
        true,
        [],
        5);
    $this->room = Room::find($roomID);
    app(JoinRoomService::class)($this->room->id, $this->player->public_id);
    DB::table('room_player')->where('player_id', $this->player->id)->update(['last_exists_at' => now()]);
});

it('should detach the player when player disconnected to the room for 30 minutes', function () {

    // 事前チェック
    $this->assertDatabaseHas('room_player', [
        'room_id' => $this->room->id,
        'player_id' => $this->player->id,
        'left_at' => null,
    ]);
    $this->assertDatabaseCount('room_player', 1);
    // 準備
    $this->travel(30)->seconds();

    // 実行
    $this->artisan('check:player-left-room');

    // アサート
    $this->assertDatabaseCount('room_player', 0);
});

it('should not set a left_at column when player disconnected to the room for 30 minutes', function () {
    // 事前チェック
    $this->assertDatabaseHas('room_player', [
        'room_id' => $this->room->id,
        'player_id' => $this->player->id,
        'left_at' => null,
    ]);
    // 準備
    $this->travel(29)->seconds();

    // 実行
    $this->artisan('check:player-left-room');

    // アサート
    $this->assertDatabaseHas('room_player', [
        'room_id' => $this->room->id,
        'player_id' => $this->player->id,
        'left_at' => null,
    ]);
});

it('should advance turn automatically when current turn player disconnected to the room', function () {
    $player2 = Player::factory()->create();

    app(JoinRoomService::class)($this->room->id, $player2->public_id);

    $room = app(RoomCompositeRepository::class)->get($this->room->id);

    // 事前チェック playerが最初のターンであることをチェック
    expect($room->getCurrentOrder())->toEqual($this->player->public_id);

    $this->travel(30)->seconds();

    // player2だけ残す
    $this->room->players()->updateExistingPivot($player2->id, ['left_at' => now()]);
    // 実行
    $this->artisan('check:player-left-room');

    $room = app(RoomCompositeRepository::class)->get($this->room->id);

    expect($room->getCurrentOrder())->toBe($player2->public_id);
});
