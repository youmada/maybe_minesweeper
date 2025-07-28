<?php

use App\Models\Player;
use App\Models\Room;
use App\Models\RoomState;
use App\Repositories\Composites\GameCompositeRepository;
use App\Services\Minesweeper\MinesweeperService;
use App\Services\Multi\CreateRoomService;
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
    app(MinesweeperService::class)->initializeGame($this->room->id, $this->width, $this->height, $this->numOfMines);

});

it('should response a game states', function () {
    $res = $this->withSession(['public_id' => $this->player->public_id])
        ->actingAs($this->player, 'magicLink')
        ->getJson("/multi/rooms/{$this->room->public_id}/play/reflection");

    $res->assertStatus(201);
    $res->assertJson([
        'data' => [
            ...app(MinesweeperService::class)->getGameStateForClient(app(GameCompositeRepository::class)->getState($this->room->id)),
        ],
    ]);
});

it('should response 401 status code when player is invalid magicLink auth', function () {
    $res = $this->withSession(['public_id' => $this->player->public_id])
        ->getJson("/multi/rooms/{$this->room->public_id}/play/reflection");

    $res->assertStatus(401);
});

it('should response 403 status code when player is not a joined room', function () {
    $this->room->players()->detach($this->player->id);
    $this->room->refresh();

    $res = $this->withSession(['public_id' => $this->player->public_id])
        ->actingAs($this->player, 'magicLink')
        ->getJson("/multi/rooms/{$this->room->public_id}/play/reflection");

    $res->assertStatus(403);
});
