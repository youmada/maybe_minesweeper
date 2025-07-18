<?php

use App\Domain\Minesweeper\GameState;
use App\Repositories\Composites\GameCompositeRepository;
use App\Repositories\DB\MinesweeperRepository as DBRepository;
use App\Repositories\Redis\MinesweeperRepository as RedisRepository;

beforeEach(function () {
    $this->redisRepository = Mockery::mock(RedisRepository::class);
    $this->dbRepository = Mockery::mock(DBRepository::class);
    $this->compositeRepository = new GameCompositeRepository($this->redisRepository, $this->dbRepository);
    $this->gameState = Mockery::mock(GameState::class);
});

it('should call getState on Redis repository, when redis repository could get game state', function () {
    $this->redisRepository->shouldReceive('getState')
        ->once()
        ->with('dummy-123')
        ->andReturn($this->gameState);

    $this->dbRepository->shouldNotReceive('getState');

    $this->compositeRepository->getState('dummy-123');
});

it('should call getState on DB repository, when redis repository could not get game state', function () {
    $this->redisRepository->shouldReceive('getState')
        ->once()
        ->with('dummy-123')
        ->andReturn(null);

    $this->dbRepository->shouldReceive('getState')
        ->with('dummy-123')
        ->once();
    $this->compositeRepository->getState('dummy-123');
});

it('should call updateState on Redis repository, when game is still playing', function () {
    $this->redisRepository->shouldReceive('updateState')
        ->once()
        ->with($this->gameState, 'dummy-123');
    $this->dbRepository->shouldNotReceive('updateState');

    $this->gameState->shouldReceive('isGameOver')
        ->once()
        ->andReturn(false);
    $this->gameState->shouldReceive('isGameClear')
        ->once()
        ->andReturn(false);

    $this->compositeRepository->updateState($this->gameState, 'dummy-123');
});

it('should call updateState on Redis repository and DB repository when game is game over', function () {
    $this->redisRepository->shouldReceive('updateState')
        ->once()
        ->with($this->gameState, 'dummy-123');

    $this->dbRepository->shouldReceive('updateState')
        ->once()
        ->with($this->gameState, 'dummy-123');

    $this->gameState->shouldReceive('isGameOver')
        ->once()
        ->andReturn(true);
    $this->gameState->shouldNotReceive('isGameClear');
    $this->compositeRepository->updateState($this->gameState, 'dummy-123');
});

it('should call updateState on Redis repository and DB repository when game is game clear', function () {
    $this->redisRepository->shouldReceive('updateState')
        ->once()
        ->with($this->gameState, 'dummy-123');

    $this->dbRepository->shouldReceive('updateState')
        ->once()
        ->with($this->gameState, 'dummy-123');

    $this->gameState->shouldReceive('isGameOver')
        ->once()
        ->andReturn(false);
    $this->gameState->shouldReceive('isGameClear')
        ->once()
        ->andReturn(true);
    $this->gameState->shouldNotReceive('isGameClear');
    $this->compositeRepository->updateState($this->gameState, 'dummy-123');
});
