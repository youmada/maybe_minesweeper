<?php

use App\Events\GameDataApplyClient;
use App\Models\Room;
use App\Repositories\Composites\GameCompositeRepository;
use App\Services\Minesweeper\MinesweeperService;

beforeEach(function () {
    $this->room = Room::factory()->create();
    $this->seedGameState($this->room->id);
    $this->gameState = app(GameCompositeRepository::class)->getState($this->room->id);
});
it('should return data payload for client', function () {
    $event = new GameDataApplyClient($this->room, $this->gameState);
    $gameState = app(GameCompositeRepository::class)->getState($this->room->id);
    expect($event->broadcastWith())->toBe(
        ['data' => app(MinesweeperService::class)->getGameStateForClient($gameState, $this->gameState)]
    );
});
