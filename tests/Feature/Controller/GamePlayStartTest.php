<?php

use App\Models\Player;
use App\Models\Room;
use App\Models\RoomState;
use App\Services\Minesweeper\MinesweeperService;
use App\Utils\UUIDFactory;

beforeEach(function () {
    $this->player = Player::factory()->create();
    $this->publicId = UUIDFactory::generate();
    $this->room = Room::factory()->create([
        'public_id' => $this->publicId,
    ]
    );
    $this->roomState = RoomState::factory()->create([
        'room_id' => $this->room->id,
        'status' => 'waiting',
    ]);
    $this->room->players()->attach($this->player->id, [
        'joined_at' => now(),
        'left_at' => null,
    ]);

    $this->width = 5;
    $this->height = 5;
    $this->numOfMines = 5;
});

it('should start game play', function () {
    $this->assertDatabaseMissing('game_states');
    $mockService = Mockery::mock(MinesweeperService::class);
    $mockService->shouldReceive('getGameStateForClient')
        ->once();

    $this->app->instance(MinesweeperService::class, $mockService);
    $response = $this->actingAs($this->player, 'magicLink')
        ->withSession(['player_id' => $this->player->session_id])
        ->post("multi/rooms/{$this->room->public_id}/play/start");

    $response->assertOk();

    $response->assertJsonStructure([
        'game' => [
            'board',
            'width',
            'height',
        ],
        'room' => [
            'turnOrder',
            'status',
        ],
    ]);
});
