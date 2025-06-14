<?php

use App\Domain\Minesweeper\TileActionMode;
use App\Domain\Room\RoomAggregate;
use App\Domain\Room\RoomStatus;
use App\Exceptions\RoomException;

beforeEach(function () {
    $this->roomId = Str::uuid()->tostring();
    $this->roomName = 'test room';
    $this->maxPlayer = 3;
    $this->players = [];
    $this->isPlivate = true;
    $this->ownerId = Str::uuid()->tostring();
    $this->flagLimit = 5;

    $this->roomAggregate = new RoomAggregate(
        $this->roomId,
        $this->roomName,
        $this->maxPlayer,
        $this->players,
        $this->isPlivate,
        $this->ownerId,
        $this->flagLimit
    );
    $this->roomAggregate->createRoom();
});

it('can not join a room when over max player', function () {
    $player1 = Str::uuid()->tostring();
    $player2 = Str::uuid()->tostring();
    $player3 = Str::uuid()->tostring();
    $player4 = Str::uuid()->tostring();
    $this->roomAggregate->join($player1);
    $this->roomAggregate->join($player2);
    $this->roomAggregate->join($player3);
    $this->roomAggregate->join($player4);
})->throws(RoomException::class);

it('can join a room when under max player', function () {
    expect($this->roomAggregate->getPlayers())->toHaveCount(0);
    $player1 = Str::uuid()->tostring();
    $this->roomAggregate->join($player1);
    expect($this->roomAggregate->getPlayers())->toHaveCount(1);
    expect($this->roomAggregate->getTurnOrder())->toEqual([$player1]);
});

it('can leave a room', function () {
    $player1 = Str::uuid()->tostring();
    $this->roomAggregate->join($player1);
    $this->roomAggregate->leave($player1);
    expect($this->roomAggregate->getPlayers())->toHaveCount(0);
    expect($this->roomAggregate->getTurnOrder())->toEqual([]);
});

it('can not leave a room when not joined', function () {
    $player1 = Str::uuid()->tostring();
    $this->roomAggregate->leave($player1);
})->throws(RoomException::class);

it('can start a game when room is ready', function () {
    $player1 = Str::uuid()->tostring();
    $this->roomAggregate->join($player1);
    $this->roomAggregate->startRoom();
    expect($this->roomAggregate->getPlayers())->toHaveCount(1)
        ->and($this->roomAggregate->getTurnOrder())->toEqual([$player1])
        ->and($this->roomAggregate->getRoomStatus())->toEqual(RoomStatus::PLAYING->value);
});

it('can be performed up to the flag limit', function () {
    // 準備
    $player1 = Str::uuid()->tostring();
    $this->roomAggregate->join($player1);
    $this->roomAggregate->startRoom();
    // 実行
    for ($i = 0; $i < $this->flagLimit; $i++) {
        $this->roomAggregate->operate($player1, TileActionMode::FLAG);
    }
    expect($this->roomAggregate->getCurrentOrder())->toEqual($player1);
});

it('can not be performed up to the flag limit', function () {
    // 準備
    $player1 = Str::uuid()->tostring();
    $this->roomAggregate->join($player1);
    $this->roomAggregate->startRoom();
    $overFlagLimit = $this->flagLimit + 1;
    // 実行
    for ($i = 0; $i < $overFlagLimit; $i++) {
        $this->roomAggregate->operate($player1, TileActionMode::FLAG);
    }
})->throws(RoomException::class);

it('can not be performed when game is not started', function () {
    $player1 = Str::uuid()->tostring();
    $this->roomAggregate->join($player1);
    $this->roomAggregate->operate($player1, TileActionMode::FLAG);
})->throws(RoomException::class);

it('can not be performed when game is end', function () {
    // 準備
    $player1 = Str::uuid()->tostring();
    $this->roomAggregate->join($player1);
    $this->roomAggregate->endRoom();
    // 実行
    $this->roomAggregate->operate($player1, TileActionMode::FLAG);
})->throws(RoomException::class);

it('can advance next turn', function () {
    // 準備
    $player1 = Str::uuid()->tostring();
    $player2 = Str::uuid()->tostring();
    $this->roomAggregate->join($player1);
    $this->roomAggregate->join($player2);
    $this->roomAggregate->startRoom();
    // 実行 & アサート
    expect($this->roomAggregate->getCurrentOrder())->toEqual($player1);
    $this->roomAggregate->nextTurn();
    expect($this->roomAggregate->getCurrentOrder())->toEqual($player2);
});

it('can go to the same player. if there is only one player', function () {
    // 準備
    $player1 = Str::uuid()->tostring();
    $this->roomAggregate->join($player1);
    $this->roomAggregate->startRoom();
    // 実行 & アサート
    expect($this->roomAggregate->getCurrentOrder())->toEqual($player1);
    $this->roomAggregate->nextTurn();
    expect($this->roomAggregate->getCurrentOrder())->toEqual($player1);
});
