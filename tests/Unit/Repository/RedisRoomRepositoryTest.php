<?php

use App\Factories\RoomAggregateFactory;
use App\Repositories\Redis\RoomRepository;
use Illuminate\Support\Facades\Redis;

beforeEach(function () {
    $this->roomId = 'room123';
    $this->roomAggregate = RoomAggregateFactory::create('TestRoom',
        4,
        'owner-id',
        Carbon\Carbon::now()->toDateString(),
        false, );
    $this->expectedKey = 'minesweeper:room:'.$this->roomId;
    $this->repository = new RoomRepository;

});

it('game states data could saved in room repository.', function () {
    // 準備
    Redis::shouldReceive('set')
        ->once()
        ->with($this->expectedKey, json_encode(['room' => $this->roomAggregate->getRoom()->toArray(), 'roomState' => $this->roomAggregate->getRoomState()->toArray()]));

    // 実行
    $this->repository->save($this->roomAggregate, $this->roomId);
});

it('game states data could get from room repository.', function () {
    Redis::shouldReceive('get')
        ->once()
        ->with($this->expectedKey)
        ->andReturn(json_encode(['room' => $this->roomAggregate->getRoom()->toArray(), 'roomState' => $this->roomAggregate->getRoomState()->toArray()]));

    $this->repository->get($this->roomId);
});

it('game states data could update in room repository.', function () {
    // 準備
    Redis::shouldReceive('set')
        ->once()
        ->with($this->expectedKey, json_encode(['room' => $this->roomAggregate->getRoom()->toArray(), 'roomState' => $this->roomAggregate->getRoomState()->toArray()]));

    // 実行
    $this->repository->update($this->roomAggregate, $this->roomId);
});

it('game states data could delete in room repository.', function () {
    Redis::shouldReceive('del')
        ->once()
        ->with($this->expectedKey);

    $this->repository->delete($this->roomId);
});
