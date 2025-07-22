<?php

namespace Tests\Unit;

use App\Domain\Minesweeper\GameService;
use App\Domain\Minesweeper\GameState;
use App\Repositories\Interfaces\GameRepositoryInterface;
use App\Services\Minesweeper\MinesweeperService;
use App\Utils\UUIDFactory;
use Exception;
use Mockery;

// ここからpestでテストを書く

beforeEach(function () {
    $this->width = 10;
    $this->height = 10;
    $this->numOfMines = 30;

    // Stub の GameState を用意
    $this->stubState = Mockery::mock(GameState::class);
    $this->stubState
        ->shouldReceive('getWidth')->andReturn($this->width)
        ->shouldReceive('getHeight')->andReturn($this->height)
        ->shouldReceive('getNumOfMines')->andReturn($this->numOfMines);

    // repository をモックして continueGame 時の振る舞いを定義
    $this->repo = Mockery::mock(GameRepositoryInterface::class);

    $this->roomId = UUIDFactory::generate();

    $this->gameService = new GameService;
    $this->service = new MinesweeperService($this->repo);
});

it('should delete game state and regenerate new game state', function () {
    $gameId = 'some-id';

    $this->repo
        ->shouldReceive('getState')
        ->with($gameId)
        ->andReturn($this->stubState);

    $this->repo
        ->shouldReceive('deleteState')
        ->with($gameId);

    $this->repo
        ->shouldReceive('saveState')
        ->with(Mockery::type(GameState::class), $gameId);

    $result = $this->service->continueGame($gameId);
    // サービスが同じインスタンスをそのまま返しているかだけを確認
    expect($result)->toBeInstanceOf(GameState::class);

});

it('should throw when repository returns null', function () {
    $gameId = 'not-found-id';

    $this->repo
        ->shouldReceive('getState')
        ->with($gameId)
        ->andReturn(null);

    // ゲームが存在しない場合は例外
    $this->service->continueGame($gameId);
})->throws(Exception::class);
