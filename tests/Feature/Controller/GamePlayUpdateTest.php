<?php

use App\Events\GameDataApplyClient;
use App\Events\GameStatesReflectionSignalEvent;
use App\Events\RoomStateApplyClientEvent;
use App\Events\RoomStatusApplyClient;
use App\Models\Player;
use App\Models\Room;
use App\Models\RoomState;
use App\Repositories\Composites\GameCompositeRepository;
use App\Repositories\Composites\RoomCompositeRepository;
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
    $this->roomState->update(['status' => 'standby']);
    app(JoinRoomService::class)($this->room->id, $this->player->public_id);
    app(MinesweeperService::class)->initializeGame($this->room->id, $this->width, $this->height, $this->numOfMines);

});

it('should update game state by open operation', function () {
    // 実行
    $response = $this->withSession(['public_id' => $this->player->public_id])
        ->actingAs($this->player, 'magicLink')
        ->put("/multi/rooms/{$this->room->public_id}/play/operate", ['x' => 1, 'y' => 1, 'operation' => 'open']);

    // アサート
    $response->assertStatus(201);
    expect(app(RoomCompositeRepository::class)->get($this->room->id)->getRoomStatus())->toEqual('playing');
});

it('should update game state by flag operation', function () {
    $this->roomState->update(['status' => 'playing']);
    $response = $this->withSession(['public_id' => $this->player->public_id])
        ->actingAs($this->player, 'magicLink')
        ->put("/multi/rooms/{$this->room->public_id}/play/operate", ['x' => 1, 'y' => 1, 'operation' => 'flag']);
    $response->assertStatus(201);
});

it('should response 403, when the status at the time for request is finished', function () {
    $this->roomState->update([
        'status' => 'finished',
    ]);
    $this->roomState->refresh();
    $response = $this->withSession(['public_id' => $this->player->public_id])
        ->actingAs($this->player, 'magicLink')
        ->put("/multi/rooms/{$this->room->public_id}/play/operate", ['x' => 1, 'y' => 1, 'operation' => 'open']);
    $response->assertStatus(403);
});

it('should response 403, when the status at the time for request is waiting', function () {
    $this->roomState->update([
        'status' => 'waiting',
    ]);
    $this->roomState->refresh();
    $response = $this->withSession(['public_id' => $this->player->public_id])
        ->actingAs($this->player, 'magicLink')
        ->put("/multi/rooms/{$this->room->public_id}/play/operate", ['x' => 1, 'y' => 1, 'operation' => 'open']);
    $response->assertStatus(403);
});

it('should validate a data', function ($x, $y, $operation, $status) {
    $response = $this->withSession(['public_id' => $this->player->public_id])
        ->actingAs($this->player, 'magicLink')
        ->put("/multi/rooms/{$this->room->public_id}/play/operate", ['x' => $x, 'y' => $y, 'operation' => $operation], [
            'Accept' => 'application/json',
        ]);
    $response->assertStatus($status);
})->with([
    ['x' => 0, 'y' => 0, 'operation' => 'open', 'status' => 201],
    ['x' => -1, 'y' => -1, 'operation' => 'invalid_operation', 'status' => 422],
    ['x' => 4, 'y' => 4, 'operation' => 'open', 'status' => 201],
    ['x' => 5, 'y' => 5, 'operation' => 'open', 'status' => 422],
    ['x' => 10, 'y' => 10, 'operation' => 'open', 'status' => 422],
]);

it('should dispatch a event when action mode is open or flag', function ($mode) {

    Event::fake();
    $this->roomState->update(['status' => 'playing']);
    $this->roomState->refresh();

    $this->withSession(['public_id' => $this->player->public_id])
        ->actingAs($this->player, 'magicLink')
        ->put("/multi/rooms/{$this->room->public_id}/play/operate", ['x' => 1, 'y' => 1, 'operation' => $mode]);

    Event::assertDispatched(GameDataApplyClient::class);
    Event::assertDispatched(RoomStateApplyClientEvent::class);
})->with([
    ['open'],
    ['flag'],
]);

it('should response 400 status code by actions flag. when room status standby', function () {
    $this->roomState->update(['status' => 'standby']);
    $this->roomState->refresh();
    $response = $this->withSession(['public_id' => $this->player->public_id])
        ->actingAs($this->player, 'magicLink')
        ->put("/multi/rooms/{$this->room->public_id}/play/operate", ['x' => 1, 'y' => 1, 'operation' => 'flag']);
    $response->assertStatus(400);
});

it('should operate a game initialize when play click is first click', function () {
    Event::fake();
    $this->roomState->update(['status' => 'standby']);
    $this->roomState->refresh();
    $response = $this->withSession(['public_id' => $this->player->public_id])
        ->actingAs($this->player, 'magicLink')
        ->put("/multi/rooms/{$this->room->public_id}/play/operate", ['x' => 1, 'y' => 1, 'operation' => 'open']);
    Event::assertDispatched(RoomStatusApplyClient::class);
    Event::assertDispatched(RoomStateApplyClientEvent::class);
    Event::assertDispatched(GameStatesReflectionSignalEvent::class);
    $response->assertStatus(201);
    $response->assertJson([
        'data' => [...app(MinesweeperService::class)->getGameStateForClient(app(GameCompositeRepository::class)->getState($this->room->id))],
    ]);
});
