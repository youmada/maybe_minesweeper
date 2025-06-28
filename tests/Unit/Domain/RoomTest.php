<?php

use App\Domain\Room\Room;

it('can join player when not max player', function () {
    $room = new Room('test room', 3, [], false, 'owner');

    $user = Str::uuid()->tostring();

    expect($room->joinRoom($user))->toBeTrue();
});

it('can not join player when max player', function () {
    $room = new Room('test room', 1, [], false, 'owner');
    $user1 = Str::uuid()->tostring();
    $user2 = Str::uuid()->tostring();
    // 1人目のユーザ
    $room->joinRoom($user1);
    expect($room->joinRoom($user2))->toBeFalse();
});

it('can leave player', function () {
    $room = new Room('test room', 2, [], false, 'owner');
    $user1 = Str::uuid()->tostring();
    $user2 = Str::uuid()->tostring();

    // まず参加させる。
    $room->joinRoom($user1);
    $room->joinRoom($user2);

    expect($room->leaveRoom($user1))->toBeTrue();
});

it('can not leave player when not joined', function () {
    $room = new Room('test room', 2, [], false, 'owner');
    $user1 = Str::uuid()->tostring();
    $user2 = Str::uuid()->tostring();

    $room->joinRoom($user1);
    $room->joinRoom($user2);

    $unjoinedUser = Str::uuid()->tostring();

    expect($room->leaveRoom($unjoinedUser))->toBeFalse();
});
