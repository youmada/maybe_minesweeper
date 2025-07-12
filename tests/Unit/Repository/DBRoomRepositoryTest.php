<?php

use App\Exceptions\RoomException;
use App\Factories\RoomAggregateFactory;
use App\Models\Player;
use App\Repositories\DB\RoomRepository;
use Illuminate\Support\Carbon;

beforeEach(function () {
    $this->roomId = '1';
    $this->roomAggregate = RoomAggregateFactory::create(
        'test room',
        3,
        'owner',
        Carbon::now()->toDateString(),
        true,
        ['owner'],
    );

    $this->roomRepository = new RoomRepository;
});

it('can save the room data in DB', function () {
    // 準備
    $dummyTime = now();
    Carbon::setTestNow(Carbon::parse($dummyTime));

    // 実行
    $room = $this->roomAggregate->getRoom();
    $roomState = $this->roomAggregate->getRoomState();
    $this->assertDatabaseMissing('rooms');
    $this->roomRepository->create($this->roomAggregate);

    // アサート
    $player = Player::where('public_id', 'owner')->first();
    $this->assertDatabaseCount('rooms', 1);
    $this->assertDatabaseCount('room_states', 1);
    $this->assertDatabaseHas('rooms', [
        'name' => $room->getName(),
        'max_player' => $room->getMaxPlayer(),
        'is_private' => true,
        'owner_id' => $player->id,
    ]);

    $this->assertDatabaseHas('room_states', [
        'turn_order' => json_encode($roomState->getTurnOrder()),
        'status' => $roomState->getStatus(),
        'flag_limit' => $roomState->getFlagLimit(),
    ]);

    $this->assertDatabaseHas('players',
        [
            'public_id' => 'owner',
        ]
    );
});

it('can not save the room data in DB', function () {
    $mock = Mockery::mock(RoomRepository::class);
    $mock
        ->shouldReceive('create')
        ->once()
        ->with($this->roomAggregate)
        ->andThrow(RoomException::class);

    $this->repository = $mock;
    $this->repository->create($this->roomAggregate);
})->throws(RoomException::class);

it('can get the room data from DB', function () {
    // 準備
    $this->roomRepository->create($this->roomAggregate);

    // 実行 & アサート
    $data = $this->roomRepository->get($this->roomId);
    expect($data)->toEqual($this->roomAggregate);
});

it('can not get the room data from DB.  because of room id is not found', function () {
    $data = $this->roomRepository->get('invalid-room-id');
    expect($data)->toBeNull();
})->throws(RoomException::class);

it('can update room data in DB', function () {
    // 準備
    $this->roomRepository->create($this->roomAggregate);
    $this->roomAggregate->startRoom();
    $this->roomAggregate->join('user1');

    // 実行
    $this->roomRepository->update($this->roomAggregate, $this->roomId);

    // アサート
    $owner = Player::where('public_id', 'owner')->first();
    $user1 = Player::where('public_id', 'user1')->first();
    $room = $this->roomAggregate->getRoom();
    $roomState = $this->roomAggregate->getRoomState();
    $this->assertDatabaseHas('rooms', [
        'name' => $room->getName(),
        'max_player' => $room->getMaxPlayer(),
        'is_private' => true,
        'owner_id' => $owner->id,
    ]);
    $this->assertDatabaseHas('room_states', [
        'turn_order' => json_encode($roomState->getTurnOrder()),
        'status' => $roomState->getStatus(),
        'flag_limit' => $roomState->getFlagLimit(),
    ]);

    $this->assertDatabaseHas('players',
        [
            'public_id' => 'owner',
        ]
    );
    $this->assertDatabaseHas('players',
        [
            'public_id' => 'user1',
        ]
    );
});

it('can not update room data in DB.  because of room id is not found', function () {
    // 準備
    $invalid_id = 'invalid-room-id';
    // 実行
    $this->roomRepository->update($this->roomAggregate, $invalid_id);
})->throws(Exception::class);

it('can delete room data in DB', function () {
    // 準備
    $this->roomRepository->create($this->roomAggregate);
    $this->assertDatabaseCount('rooms', 1);
    $this->assertDatabaseCount('room_states', 1);
    // 実行
    $this->roomRepository->delete($this->roomId);
    // アサート
    $this->assertDatabaseCount('rooms', 0);
    $this->assertDatabaseCount('room_states', 0);
});

it('can not delete room data in DB.  because of room id is not found', function () {
    // 準備
    $invalid_id = 'invalid-room-id';
    // 実行
    $this->roomRepository->delete($invalid_id);
})->throws(RoomException::class);
