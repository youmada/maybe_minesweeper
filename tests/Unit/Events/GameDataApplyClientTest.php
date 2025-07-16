<?php

use App\Events\GameDataApplyClient;
use App\Models\Room;
use App\Repositories\Composites\GameCompositeRepository;

beforeEach(function () {
    $this->room = Room::factory()->create();
    $this->seedGameState($this->room->id);
});
it('should return data payload for client', function () {
    $event = new GameDataApplyClient($this->room);
    $gameState = app(GameCompositeRepository::class)->getState($this->room->id);
    expect($event->broadcastWith())->toBe(
        ['data' => $gameState->toClientArray()]
    );
});
