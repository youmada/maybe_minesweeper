<?php

use App\Domain\Room\RoomStatus;
use App\Events\GameDataApplyClient;
use App\Events\RoomStateApplyClientEvent;
use App\Events\RoomStatusApplyClient;
use App\Models\GameState;
use App\Models\Player;
use App\Models\Room;
use App\Models\RoomState;
use App\Services\Minesweeper\MinesweeperService;
use App\Services\Multi\CreateRoomService;
use App\Services\Multi\JoinRoomService;
use Carbon\Carbon;

beforeEach(function () {
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
    $this->roomState->update(['status' => RoomStatus::FINISHED->value]);
    $this->gameState = GameState::where('room_id', $this->room->id)->first();
    $this->gameState->update(['is_game_started' => true, 'is_game_over' => true, 'is_game_clear' => false]);
    $this->roomState->refresh();
    $this->gameState->refresh();
});

it('should continue a game controller response 201, when the previous game is clear or failure', function () {
    // 実行
    $response = $this->withSession(['public_id' => $this->player->public_id])
        ->actingAs($this->player, 'magicLink')
        ->postJson("/multi/rooms/{$this->room->public_id}/play/continue");
    // アサート
    $response->assertStatus(201);
});

it('should game and room status change a continue state', function () {
    // 事前チェック
    $this->assertDatabaseHas('room_states', [
        'room_id' => $this->room->id,
        'status' => RoomStatus::FINISHED->value,
    ]);
    $this->assertDatabaseHas('game_states', [
        'room_id' => (int) $this->room->id,
        'is_game_started' => 1,
        'is_game_over' => 1,
        'is_game_clear' => 0,
    ]);

    $this->assertDatabaseCount('room_states', 1);
    $this->assertDatabaseCount('game_states', 1);

    // 実行
    $this->withSession(['public_id' => $this->player->public_id])
        ->actingAs($this->player, 'magicLink')
        ->postJson("/multi/rooms/{$this->room->public_id}/play/continue");

    // アサート
    $this->assertDatabaseCount('room_states', 1);
    // 削除して再生成するので、総数は変わらない。
    $this->assertDatabaseCount('game_states', 1);

    $this->assertDatabaseHas('room_states', [
        'room_id' => $this->room->id,
        'status' => RoomStatus::STANDBY->value,
    ]);
    $this->assertDatabaseHas('game_states', [
        'room_id' => (int) $this->room->id,
        'is_game_started' => 0,
        'is_game_over' => 0,
        'is_game_clear' => 0,
    ]);
});

it('should not a route access, when magicLink authentication is invalid', function () {
    $response = $this->withSession(['public_id' => $this->player->public_id])
        ->postJson("/multi/rooms/{$this->room->public_id}/play/continue");

    $response->assertStatus(401);
});

it('should not a route access, when magicLink middleware process is failure', function () {
    $response = $this->withSession(['public_id' => 'invalid_public_id'])
        ->actingAs($this->player, 'magicLink')
        ->postJson("/multi/rooms/{$this->room->public_id}/play/continue");

    $response->assertStatus(403);
});

it('should dispatch the room status and room data for client event', function () {
    Event::fake();
    $this->withSession(['public_id' => $this->player->public_id])
        ->actingAs($this->player, 'magicLink')
        ->postJson("/multi/rooms/{$this->room->public_id}/play/continue");

    Event::assertDispatched(RoomStateApplyClientEvent::class);
    Event::assertDispatched(RoomStatusApplyClient::class);
    Event::assertDispatched(GameDataApplyClient::class);
});
