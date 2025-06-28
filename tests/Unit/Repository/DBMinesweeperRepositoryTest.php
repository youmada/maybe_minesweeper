<?php

use App\Domain\Minesweeper\Board;
use App\Domain\Minesweeper\GameState;
use App\Exceptions\RepositoryException;
use App\Models\GameState as DBGameState;
use App\Models\Room;
use App\Repositories\DB\MinesweeperRepository;
use App\Utils\UUIDFactory;

beforeEach(function () {

    $room = Room::factory()->create();

    $this->gameID = UUIDFactory::generate();
    $this->roomId = $room->id;
    $this->expectedData = [
        'width' => 10,
        'height' => 10,
        'numOfMines' => 20,
        'tileStates' => json_encode([]), // Board の空配列をシリアライズ
        'isGameStarted' => false,
        'isGameClear' => false,
        'isGameOver' => false,
    ];
    $this->stateStub = $this->createStub(GameState::class);
    // 1. テスト用の期待データを準備
    $this->stateStub->method('getWidth')->willReturn($this->expectedData['width']);
    $this->stateStub->method('getHeight')->willReturn($this->expectedData['height']);
    $this->stateStub->method('getNumOfMines')->willReturn($this->expectedData['numOfMines']);
    $this->stateStub->method('getGameState')->willReturn(json_decode($this->expectedData['tileStates']));
    $this->stateStub->method('isGameStarted')->willReturn((bool) $this->expectedData['isGameStarted']);
    $this->stateStub->method('isGameClear')->willReturn((bool) $this->expectedData['isGameClear']);
    $this->stateStub->method('isGameOver')->willReturn((bool) $this->expectedData['isGameOver']);
    $this->stateStub->method('toArray')->willReturn($this->expectedData);

    $this->repository = new MinesweeperRepository;
});

it('can save the state in DB', function () {
    $this->repository->saveState($this->stateStub, $this->roomId);
    $savedGameState = DBGameState::where('room_id', $this->roomId)->first();
    foreach ($this->expectedData as $key => $value) {
        expect($savedGameState[$key])->toBe($value);
    }
});

it('can be skipped if the state already exists in DB', function () {
    // まずゲームステートを保存
    $this->repository->saveState($this->stateStub, $this->roomId);
    $this->assertDatabaseCount('game_states', 1);

    // 2回目に同じIDで保存する場合、処理がスキップされる。
    $this->repository->saveState($this->stateStub, $this->roomId);
    // カウントが変わらないので、スキップされたことと同じ。
    $this->assertDatabaseCount('game_states', 1);
});

it("can't save the state in DB", function () {
    $mock = Mockery::mock(MinesweeperRepository::class);
    $mock
        ->shouldReceive('saveState')
        ->once()
        ->with($this->stateStub, $this->roomId)
        ->andThrow(RepositoryException::class);

    $this->repository = $mock;
    $this->repository->saveState($this->stateStub, $this->roomId);
})->throws(RepositoryException::class);

it('can get the state class from DB',
    function () {
        $board = new Board(10, 10);
        $gameState = new GameState($board, 10, 10, 20);
        $this->repository->saveState($gameState, $this->roomId);

        expect($gameState)->toEqual($this->repository->getState($this->roomId));
    });

it("can't get the state class from DB", function () {
    $gameState = $this->repository->getState('invalid-game-id');
    expect($gameState)->toBeNull();
});

it('can update the state in DB', function () {
    $board = new Board(10, 10);
    $gameState = new GameState($board, 10, 10, 20);
    $this->repository->saveState($gameState, $this->roomId);
    // ゲーム開始状態に遷移させる。
    $gameState->startGame();

    $this->repository->updateState($gameState, $this->roomId);
    $savedGameState = DBGameState::where('room_id', $this->roomId)->first();
    expect($savedGameState->is_game_started)->toBeTrue();
});

it("can't update the state in DB. because of game id is not found", function () {
    $invalid_id = 'invalid-game-id';

    $this->repository->updateState($this->stateStub, $invalid_id);
})->throws(RepositoryException::class);

it('can delete the state in DB', function () {
    $this->repository->saveState($this->stateStub, $this->roomId);
    $this->repository->deleteState($this->roomId);
    $savedGameState = DBGameState::where('room_id', $this->roomId)->first();
    expect($savedGameState)->toBeNull();
});

it("can't delete the state in DB. because of game id is not found", function () {
    $invalid_id = 'invalid-game-id';
    $this->repository->deleteState($invalid_id);
})->throws(RepositoryException::class);
