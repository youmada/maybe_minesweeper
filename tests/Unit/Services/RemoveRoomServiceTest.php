<?php

use App\Repositories\Interfaces\RoomCompositesRepositoryInterface;
use App\Services\Multi\RemoveRoomService;

beforeEach(function () {
    $this->mockRoomRepository = Mockery::mock(RoomCompositesRepositoryInterface::class);
    $this->mockRoomRepository->shouldReceive('delete')
        ->once()
        ->with('room-123');
});

it('removes a multi room successfully
', function () {
    // 実行
    $service = new RemoveRoomService($this->mockRoomRepository);

    $service('room-123');
});
