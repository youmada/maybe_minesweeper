<?php

use App\Domain\Minesweeper\GameService;
use App\Domain\Minesweeper\GameState;
use App\Repositories\Interfaces\GameRepositoryInterface;
use App\Services\Minesweeper\MinesweeperService;

// ここからpestでテストを書く

beforeEach(function () {

    // Stub の GameState を用意
    $this->stubState = Mockery::mock(GameState::class);

    // repository をモックして continueGame 時の振る舞いを定義
    $this->repo = Mockery::mock(GameRepositoryInterface::class);

    $this->width = 10;
    $this->height = 10;
    $this->numOfMines = 30;

    $this->gameService = new GameService;
    $this->service = new MinesweeperService($this->gameService, $this->repo);
});

it('should return GameState when repository has it', function () {
    $gameId = 'some-id';

    // getState が stubState を返すように設定
    $this->repo
        ->shouldReceive('getState')
        ->with($gameId)
        ->andReturn($this->stubState);

    $result = $this->service->continueGame($gameId);

    // サービスが同じインスタンスをそのまま返しているかだけを確認
    expect($result)->toBe($this->stubState);
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
