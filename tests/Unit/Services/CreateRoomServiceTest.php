<?php

use App\Domain\Room\RoomAggregate;
use App\Factories\RoomAggregateFactory;
use App\Repositories\Interfaces\RoomCompositesRepositoryInterface;
use App\Services\Multi\CreateRoomService;
use Carbon\Carbon;

beforeEach(function () {
    // モックを準備
    $this->mockRoomFactory = Mockery::mock(RoomAggregateFactory::class);
    $this->mockRoomFactory->shouldReceive('create')
        ->once()
        ->with('TestRoom', 4, 'owner-id', Carbon::now()->toDateString(), false, [], 5)
        ->andReturn($this->mockRoomAggregate = Mockery::mock(RoomAggregate::class));

    $this->mockRoomRepository = Mockery::mock(RoomCompositesRepositoryInterface::class);
    $this->mockRoomRepository->shouldReceive('create')
        ->once()
        ->with($this->mockRoomAggregate)
        ->andReturn(true);
});

it('creates a multi room successfully
', function () {
    // 準備
    $this->assertDatabaseMissing('rooms');
    $this->assertDatabaseMissing('room_states');
    $this->assertDatabaseMissing('room_users');
    // 実行
    $service = new CreateRoomService($this->mockRoomRepository, $this->mockRoomFactory);

    $service('TestRoom', 4, 'owner-id', Carbon::now()->toDateString(), false, [], 5);
});
