<?php

namespace Tests\Unit\Domain;

use App\Domain\Minesweeper\TileActionMode;
use App\Domain\Room\RoomState;
use App\Domain\Room\RoomStatus;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->flagLimit = 5;
    $this->roomState = new RoomState(
        [],
        0,
        RoomStatus::WAITING,
        0,
        false,
        $this->flagLimit);
    $this->user1 = Str::uuid()->tostring();
    $this->user2 = Str::uuid()->tostring();
    $this->user3 = Str::uuid()->tostring();
    $this->turnOrder = [$this->user1, $this->user2, $this->user3];
    $this->roomState->initializeTurnOrder($this->turnOrder);
});

it('can change room status',
    function () {
        $roomState = new RoomState(
            [],
            0,
            RoomStatus::WAITING,
            0,
            false,
            5);
        $roomState->changeStatus(RoomStatus::PLAYING);
        expect($roomState->getStatus())->toEqual(RoomStatus::PLAYING->value);
    });

it('can initialize room turn order', function () {
    expect($this->roomState->getTurnOrder())->toHaveCount(3)
        ->and($this->roomState->getTurnOrder())->toEqual($this->turnOrder);
});

it('can add user to room turn order', function () {
    $roomState = new RoomState(
        [],
        0,
        RoomStatus::WAITING,
        0,
        false, 5);
    $user1 = Str::uuid()->tostring();
    $user2 = Str::uuid()->tostring();
    $turnOrder = [$user1];
    $roomState->initializeTurnOrder($turnOrder);
    $roomState->pushTurnOrder($user2);

    expect($roomState->getTurnOrder())->toHaveCount(2)
        ->and($roomState->getTurnOrder())->toEqual([$user1, $user2]);
});

it('can remove user from room turn order', function () {
    $roomState = new RoomState(
        [],
        0,
        RoomStatus::WAITING,
        0,
        false, 5);
    $user1 = Str::uuid()->tostring();
    $user2 = Str::uuid()->tostring();
    $turnOrder = [$user1, $user2];
    $roomState->initializeTurnOrder($turnOrder);
    $roomState->removeTurnOrder($user2);

    expect($roomState->getTurnOrder())->toHaveCount(1)
        ->and($roomState->getTurnOrder())->toEqual([$user1]);
});

it('can remove user from room turn order anywhere', function () {
    $this->roomState->removeTurnOrder($this->user2);

    expect($this->roomState->getTurnOrder())->toHaveCount(2)
        ->and($this->roomState->getTurnOrder())->toEqual([$this->user1, $this->user3]);
});

it('can turn next turn order', function () {
    $this->roomState->changeStatus(RoomStatus::PLAYING);
    expect($this->roomState->getCurrentOrder())->toEqual($this->user1);
    $this->roomState->processRoomAction(TileActionMode::OPEN);

    $this->roomState->nextTurn();

    expect($this->roomState->getCurrentOrder())->toEqual($this->user2);
});

it('should turn around when last order', function () {
    // 1回目
    expect($this->roomState->getCurrentOrder())->toEqual($this->user1);
    $this->roomState->processRoomAction(TileActionMode::OPEN);
    $this->roomState->nextTurn();
    // 2回目
    $this->roomState->processRoomAction(TileActionMode::OPEN);
    $this->roomState->nextTurn();
    expect($this->roomState->getCurrentOrder())->toEqual($this->user3);
    // 3回目
    $this->roomState->processRoomAction(TileActionMode::OPEN);
    $this->roomState->nextTurn();
    expect($this->roomState->getCurrentOrder())->toEqual($this->user1);
});

it('can increase flag count', function () {
    // 実行
    $this->roomState->processRoomAction(TileActionMode::FLAG);

    // アサート
    expect($this->roomState->getActionState()['flagCount'])->toEqual(1);
});

it('can tileFlag turn true. when tile opened', function () {
    // 実行
    $this->roomState->processRoomAction(TileActionMode::OPEN);
    // アサート

    expect($this->roomState->getActionState()['tileOpened'])->toBeTrue();
});

it('can reset action state when advance a next turn', function () {
    // 準備
    for ($i = 0; $i < 5; $i++) {
        $this->roomState->processRoomAction(TileActionMode::FLAG);
    }
    $this->roomState->processRoomAction(TileActionMode::OPEN);
    // 実行
    $this->roomState->nextTurn();

    // アサート
    expect($this->roomState->getActionState())->toEqual([
        'flagCount' => 0,
        'tileOpened' => false,
    ]);
});

it('can not process action when current order is not in turn order', function () {
    expect($this->roomState->getCurrentOrder())->toEqual($this->user1);
    $this->roomState->processRoomAction(TileActionMode::OPEN);
    $this->roomState->nextTurn();
    expect($this->roomState->getCurrentOrder())->toEqual($this->user2);
    expect($this->roomState->canOperate($this->user1))->toBeFalse();

});
