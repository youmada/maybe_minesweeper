<?php

use App\Domain\Room\RoomAggregate;
use App\Domain\Room\RoomState;
use App\Domain\Room\RoomStatus;
use App\Repositories\Composites\RoomCompositeRepository;
use App\Repositories\DB\RoomRepository as DBRepository;
use App\Repositories\Redis\RoomRepository as RedisRepository;

beforeEach(function () {
    $this->redisRepository = Mockery::mock(RedisRepository::class);
    $this->dbRepository = Mockery::mock(DBRepository::class);
    $this->compositeRepository = new RoomCompositeRepository($this->redisRepository, $this->dbRepository);
    $this->roomState = Mockery::mock(RoomState::class);
});

it('should call get on Redis repository during PLAYING', function () {
    $this->redisRepository->shouldReceive('get')
        ->once()
        ->with('dummy-123')
        ->andReturn($this->roomState);

    $this->roomState->shouldReceive('getStatus')
        ->once()
        ->andReturn(RoomStatus::PLAYING->value);

    $this->dbRepository->shouldNotReceive('get');

    $this->compositeRepository->get('dummy-123');
});

it('should call get on DB repository during PLAYING', function () {
    $this->redisRepository->shouldReceive('get')
        ->once()
        ->with('dummy-123')
        ->andReturn($this->roomState);

    $this->roomState->shouldReceive('getStatus')
        ->once()
        ->andReturn(RoomStatus::FINISHED->value);

    $this->dbRepository->shouldReceive('get')
        ->with('dummy-123')
        ->once();
    $this->compositeRepository->get('dummy-123');
});

it('should call update on Redis repository from arguments RoomAggregate', function () {
    $this->redisRepository->shouldReceive('update')
        ->once()
        ->with($this->roomState, 'dummy-123');
    $this->dbRepository->shouldNotReceive('update');
    $this->compositeRepository->update($this->roomState, 'dummy-123');
});

it('should call update on DB repository from arguments RoomState', function () {
    $roomAggregate = Mockery::mock(RoomAggregate::class);
    $this->redisRepository->shouldNotReceive('update');
    $this->dbRepository->shouldReceive('update')
        ->once()
        ->with($roomAggregate, 'dummy-123');
    $this->compositeRepository->update($roomAggregate, 'dummy-123');
});
