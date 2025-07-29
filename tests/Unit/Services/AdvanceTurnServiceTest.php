<?php

use App\Domain\Minesweeper\TileActionMode;
use App\Domain\Room\RoomAggregate;
use App\Repositories\Interfaces\RoomCompositesRepositoryInterface;
use App\Services\Multi\AdvanceTurnService;

beforeEach(function () {
    $this->mockRoomAggregate = Mockery::mock(RoomAggregate::class);
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
    $this->mockRoomAggregate->shouldReceive('operate')
        ->once()
        ->with('user-123', TileActionMode::OPEN);
    $this->mockRoomAggregate->shouldReceive('isTurnFinished')
        ->once()
        ->andReturn(true);
    $this->mockRoomAggregate->shouldReceive('nextTurn')
        ->once();
    $service = new AdvanceTurnService($this->mockRoomRepository);
    $service('room-123', 'user-123', TileActionMode::OPEN);
});

it('can not advance turn. when a turn actions flag mode', function () {
    $this->mockRoomAggregate->shouldReceive('operate')
        ->once()
        ->with('user-123', TileActionMode::FLAG);
    $this->mockRoomAggregate->shouldReceive('isTurnFinished')
        ->once()
        ->andReturn(false);
    $this->mockRoomAggregate->shouldReceive('nextTurn')
        ->never();
    $service = new AdvanceTurnService($this->mockRoomRepository);
    $service('room-123', 'user-123', TileActionMode::FLAG);
});
