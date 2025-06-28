<?php

use App\Factories\RoomStateFactory;
use App\Repositories\Redis\RoomRepository;
use Illuminate\Support\Facades\Redis;

beforeEach(function () {
    $this->roomId = 'room123';
    $this->roomState = RoomStateFactory::createNew(['user1']);
    $this->expectedKey = 'minesweeper:room:'.$this->roomId;
    $this->repository = new RoomRepository;

});

it('game states data could saved in room repository.', function () {
    // 準備
    Redis::shouldReceive('set')
        ->once()
        ->with($this->expectedKey, json_encode($this->roomState->toArray()));

    // 実行
    $this->repository->save($this->roomState, $this->roomId);
});

it('game states data could get from room repository.', function () {
    Redis::shouldReceive('get')
        ->once()
        ->with($this->expectedKey)
        ->andReturn($this->roomState->toArray());

    $this->repository->get($this->roomId);
});

it('game states data could update in room repository.', function () {
    // 準備
    Redis::shouldReceive('set')
        ->once()
        ->with($this->expectedKey, json_encode($this->roomState->toArray()));

    // 実行
    $this->repository->update($this->roomState, $this->roomId);
});

it('game states data could delete in room repository.', function () {
    Redis::shouldReceive('del')
        ->once()
        ->with($this->expectedKey);

    $this->repository->delete($this->roomId);
});
