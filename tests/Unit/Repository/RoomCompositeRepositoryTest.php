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
    //    $this->roomState = Mockery::mock(RoomState::class);
    $this->roomAggregate = Mockery::mock(RoomAggregate::class);
    //    $this->room = Mockery::mock(RoomState::class);
});

it('should call get on Redis repository during PLAYING', function () {
    $this->redisRepository->shouldReceive('get')
        ->once()
        ->with('dummy-123')
        ->andReturn($this->roomAggregate);

    $this->roomAggregate->shouldReceive('getRoomStatus')
        ->once()
        ->andReturn(RoomStatus::PLAYING->value);

    $this->dbRepository->shouldNotReceive('get');

    $this->compositeRepository->get('dummy-123');
});

it('should call get on DB repository during PLAYING', function () {
    $this->redisRepository->shouldReceive('get')
        ->once()
        ->with('dummy-123')
        ->andReturn($this->roomAggregate);

    $this->roomAggregate->shouldReceive('getRoomStatus')
        ->once()
        ->andReturn(RoomStatus::FINISHED->value);

    $this->dbRepository->shouldReceive('get')
        ->with('dummy-123')
        ->once();
    $this->compositeRepository->get('dummy-123');
});