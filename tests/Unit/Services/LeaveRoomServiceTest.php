<?php

use App\Domain\Room\RoomAggregate;
use App\Repositories\Interfaces\RoomCompositesRepositoryInterface;
use App\Services\Multi\LeaveRoomService;

beforeEach(function () {
    $this->mockRoomAggregate = Mockery::mock(RoomAggregate::class);
    $this->mockRoomAggregate->shouldReceive('leave')
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

it('exits a specific user from a room', function () {
    $service = new LeaveRoomService($this->mockRoomRepository);
    $service('room-123', 'user-123');
});
