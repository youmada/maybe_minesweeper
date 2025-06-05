<?php

use App\Domain\Minesweeper\GameState;
use App\Exceptions\RepositoryException;
use App\Models\GameState as DBGameState;
use App\Repositories\DB\MinesweeperRepository;

beforeEach(function () {

    $this->gameID = uuid_create(UUID_TYPE_RANDOM);

    $this->expectedData = [
        'width' => 10,
        'height' => 10,
        'num_of_mines' => 20,
        'tile_states' => json_encode([]), // Board の空配列をシリアライズ
        'game_id' => $this->gameID,
        'is_game_started' => 0, // false は DB 内で 0 に変換される
        'is_game_clear' => 0,
        'is_game_over' => 0,
    ];
    $this->stateStub = $this->createStub(GameState::class);
    // 1. テスト用の期待データを準備
    $this->stateStub->method('getWidth')->willReturn($this->expectedData['width']);
    $this->stateStub->method('getHeight')->willReturn($this->expectedData['height']);
    $this->stateStub->method('getNumOfMines')->willReturn($this->expectedData['num_of_mines']);
    $this->stateStub->method('getGameState')->willReturn(json_decode($this->expectedData['tile_states']));
    $this->stateStub->method('isGameStarted')->willReturn((bool) $this->expectedData['is_game_started']);
    $this->stateStub->method('isGameClear')->willReturn((bool) $this->expectedData['is_game_clear']);
    $this->stateStub->method('isGameOver')->willReturn((bool) $this->expectedData['is_game_over']);

    $this->repository = new MinesweeperRepository;

});

it('can save the state in DB', function () {
    $this->repository->saveState($this->stateStub, $this->gameID);
    $savedGameState = DBGameState::where('game_id', $this->gameID)->first();
    foreach ($this->expectedData as $key => $value) {
        expect($savedGameState->{$key})->toBe($value);
    }

});

it('can be skipped if the state already exists in DB', function () {
    // まずゲームステートを保存
    $this->repository->saveState($this->stateStub, $this->gameID);
    $this->assertDatabaseCount('game_states', 1);

    // 2回目に同じIDで保存する場合、処理がスキップされる。
    $this->repository->saveState($this->stateStub, $this->gameID);
    // カウントが変わらないので、スキップされたことと同じ。
    $this->assertDatabaseCount('game_states', 1);
});

it("can't save the state in DB", function () {
    $mock = Mockery::mock(MinesweeperRepository::class);
    $mock
        ->shouldReceive('saveState')
        ->once()
        ->with($this->stateStub, $this->gameID)
        ->andThrow(RepositoryException::class);

    $this->repository = $mock;
    $this->repository->saveState($this->stateStub, $this->gameID);
})->throws(RepositoryException::class);

it('can get the state from DB', function () {});
