<?php

use App\Domain\Room\RoomAggregate;
use App\Repositories\Interfaces\RoomCompositesRepositoryInterface;
use App\Services\Multi\JoinRoomService;

beforeEach(function () {
    $this->mockRoomAggregate = Mockery::mock(RoomAggregate::class);
    $this->mockRoomAggregate->shouldReceive('join')
        ->once()
        ->with('user-123');
    $this->mockRoomRepository = Mockery::mock(RoomCompositesRepositoryInterface::class);
    $this->mockRoomRepository->shouldReceive('get')
        ->once()
        ->with('room-123')
        ->andReturn($this->mockRoomAggregate);
    $this->mockRoomRepository->shouldReceive('update')
        ->once()
        ->with($this->mockRoomAggregate, 'room-123');
});

it('new player can join multi room', function () {
    $service = new JoinRoomService($this->mockRoomRepository);
    $service('room-123', 'user-123');
});
