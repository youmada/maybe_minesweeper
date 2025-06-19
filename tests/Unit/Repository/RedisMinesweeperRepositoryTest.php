<?php

use App\Domain\Minesweeper\Board;
use App\Domain\Minesweeper\GameState;
use App\Repositories\Redis\MinesweeperRepository;
use Illuminate\Support\Facades\Redis;

beforeEach(function () {
    $this->gameID = 'game123';
    $this->roomId = 'room123';
    $this->gameState = [
        'width' => 10,
        'height' => 10,
        'numOfMines' => 20,
        'tileStates' => (new Board(10, 10))->toArray(),
        'isGameStarted' => false,
        'isGameClear' => false,
        'isGameOver' => false,
    ];

    $this->stateStub = $this->createStub(GameState::class);
    $this->stateStub->method('toArray')->willReturn($this->gameState);

    $this->expectedKey = 'minesweeper:game:'.$this->gameID;
    $this->expectedData = json_encode($this->gameState);

    $this->repository = new MinesweeperRepository;

});

it('game states data could saved in minesweeper repository.', function () {
    // 準備
    Redis::shouldReceive('set')
        ->once()
        ->with($this->expectedKey, $this->expectedData);

    // 実行
    $this->repository->saveState($this->stateStub, $this->gameID, $this->roomId);
});

it('game states data could get from minesweeper repository.', function () {
    Redis::shouldReceive('get')
        ->once()
        ->with($this->expectedKey)
        ->andReturn($this->expectedData);

    $this->repository->getState($this->gameID);
});

it('game states data could update in minesweeper repository.', function () {
    // 準備
    Redis::shouldReceive('set')
        ->once()
        ->with($this->expectedKey, $this->expectedData);

    // 実行
    $this->repository->updateState($this->stateStub, $this->gameID);
});

it('game states data could delete in minesweeper repository.', function () {
    Redis::shouldReceive('del')
        ->once()
        ->with($this->expectedKey);

    $this->repository->deleteState($this->gameID);
});
