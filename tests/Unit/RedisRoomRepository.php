<?php

use App\Repositories\Redis\RoomRepository;
use Illuminate\Support\Facades\Redis;

beforeEach(function () {
    $this->roomId = 'room123';
    $this->userId = fake()->uuid();

    $this->expectedKey = 'minesweeper:room:'.$this->roomId;
    $this->repository = new RoomRepository;

});

it('game states data could saved in room repository.', function () {
    // 準備
    Redis::shouldReceive('set')
        ->once()
        ->with($this->expectedKey, $this->userId);

    // 実行
    $this->repository->saveState($this->userId, $this->roomId);
});

it('game states data could get from room repository.', function () {
    Redis::shouldReceive('get')
        ->once()
        ->with($this->expectedKey)
        ->andReturn($this->userId);

    $this->repository->getState($this->roomId);
});

it('game states data could update in room repository.', function () {
    // 準備
    Redis::shouldReceive('set')
        ->once()
        ->with($this->expectedKey, $this->userId);

    // 実行
    $this->repository->updateState($this->userId, $this->roomId);
});

it('game states data could delete in room repository.', function () {
    Redis::shouldReceive('del')
        ->once()
        ->with($this->expectedKey);

    $this->repository->deleteState($this->roomId);
});
