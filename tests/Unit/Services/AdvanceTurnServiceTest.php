<?php

use App\Domain\Minesweeper\TileActionMode;
use App\Domain\Room\RoomAggregate;
use App\Repositories\Interfaces\RoomCompositesRepositoryInterface;
use App\Services\Multi\AdvanceTurnService;

beforeEach(function () {
    $this->mockRoomAggregate = Mockery::mock(RoomAggregate::class);
    $this->mockRoomAggregate->shouldReceive('operate')
        ->once()
        ->with('user-123', TileActionMode::FLAG);
    $this->mockRoomRepository = Mockery::mock(RoomCompositesRepositoryInterface::class);
    $this->mockRoomRepository->shouldReceive('get')
        ->once()
        ->with('room-123')
        ->andReturn($this->mockRoomAggregate);
    $this->mockRoomRepository->shouldReceive('update')
        ->once()
        ->with($this->mockRoomAggregate, 'room-123');
});

it('can advance turn by action', function () {
    $service = new AdvanceTurnService($this->mockRoomRepository, $this->mockRoomAggregate);
    $service('room-123', 'user-123', TileActionMode::FLAG);
});
