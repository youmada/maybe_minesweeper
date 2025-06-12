<?php

use App\Domain\Room\RoomState;
use App\Domain\Room\RoomStatus;

test('', function () {
    expect(true)->toBeTrue();
});

beforeEach(function () {
    $this->roomId = Str::uuid()->tostring();
    $this->roomState = new RoomState($this->roomId, [], RoomStatus::WAITING);
    $this->user1 = Str::uuid()->tostring();
    $this->user2 = Str::uuid()->tostring();
    $this->user3 = Str::uuid()->tostring();
    $this->turnOrder = [$this->user1, $this->user2, $this->user3];
    $this->roomState->initializeTurnOrder($this->turnOrder);
});

it('can change room status', function () {
    $roomState = new RoomState($this->roomId, [], RoomStatus::WAITING);
    $roomState->changeStatus(RoomStatus::PLAYING);
    expect($roomState->getStatus())->toEqual(RoomStatus::PLAYING->value);
});

it('can initialize room turn order', function () {
    expect($this->roomState->getTurnOrder())->toHaveCount(3)
        ->and($this->roomState->getTurnOrder())->toEqual($this->turnOrder);
});

it('can add user to room turn order', function () {
    $roomState = new RoomState($this->roomId, [], RoomStatus::WAITING);
    $user1 = Str::uuid()->tostring();
    $user2 = Str::uuid()->tostring();
    $turnOrder = [$user1];
    $roomState->initializeTurnOrder($turnOrder);
    $roomState->pushTurnOrder($user2);

    expect($roomState->getTurnOrder())->toHaveCount(2)
        ->and($roomState->getTurnOrder())->toEqual([$user1, $user2]);
});

it('can remove user from room turn order', function () {
    $roomState = new RoomState($this->roomId, [], RoomStatus::WAITING);
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

    $this->roomState->nextTurn();

    expect($this->roomState->getCurrentOrder())->toEqual($this->user2);
});

it('should turn around when last order', function () {
    expect($this->roomState->getCurrentOrder())->toEqual($this->user1);
    $this->roomState->nextTurn();
    $this->roomState->nextTurn();
    expect($this->roomState->getCurrentOrder())->toEqual($this->user3);

    $this->roomState->nextTurn();
    expect($this->roomState->getCurrentOrder())->toEqual($this->user1);

});
