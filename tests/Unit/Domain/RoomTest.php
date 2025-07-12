<?php

use App\Domain\Room\Room;
use Carbon\Carbon;

beforeEach(function () {
    $this->room = new Room('test room', 3, [], Carbon::now()->toDateString(), false, 'owner');
});

it('can join player when not max player', function () {

    $user = Str::uuid()->tostring();

    expect($this->room->joinRoom($user))->toBeTrue();
});

it('can not join player when max player', function () {
    $room = new Room('test room', 1, [], 4, false, 'owner');
    $user1 = Str::uuid()->tostring();
    $user2 = Str::uuid()->tostring();
    // 1人目のユーザ
    $room->joinRoom($user1);
    expect($room->joinRoom($user2))->toBeFalse();
});

it('can leave player', function () {
    $user1 = Str::uuid()->tostring();
    $user2 = Str::uuid()->tostring();

    // まず参加させる。
    $this->room->joinRoom($user1);
    $this->room->joinRoom($user2);

    expect($this->room->leaveRoom($user1))->toBeTrue();
});

it('can not leave player when not joined', function () {
    $user1 = Str::uuid()->tostring();
    $user2 = Str::uuid()->tostring();

    $this->room->joinRoom($user1);
    $this->room->joinRoom($user2);

    $unjoinedUser = Str::uuid()->tostring();

    expect($this->room->leaveRoom($unjoinedUser))->toBeFalse();
});
