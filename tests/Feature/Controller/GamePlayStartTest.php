<?php

use App\Domain\Room\RoomStatus;
use App\Factories\RoomAggregateFactory;
use App\Models\Player;
use App\Models\Room;
use App\Models\RoomState;
use App\Repositories\Composites\RoomCompositeRepository;
use App\Utils\UUIDFactory;

beforeEach(function () {
    $this->player = Player::factory()->create();
    $this->publicId = UUIDFactory::generate();
    $this->room = Room::factory()->create([
        'public_id' => $this->publicId,
    ]
    );
    $this->roomState = RoomState::factory()->create([
        'room_id' => $this->room->id,
        'status' => 'waiting',
    ]);
    $this->room->players()->attach($this->player->id, [
        'joined_at' => now(),
        'left_at' => null,
    ]);

    $room = RoomAggregateFactory::create($this->room->name,
        3,
        $this->player->public_id,
        \Carbon\Carbon::now()->addDay(),
        true,
        [$this->player->public_id],
        5);
    app(RoomCompositeRepository::class)->update($room, $this->room->id);

    $this->width = 5;
    $this->height = 5;
    $this->numOfMines = 5;
});

it('should start game play', function () {
    $this->assertDatabaseHas('room_states', [
        'room_id' => $this->room->id,
        'status' => RoomStatus::WAITING->value,
    ]);
    $response = $this->actingAs($this->player, 'magicLink')
        ->withSession(['public_id' => $this->player->public_id])
        ->post("multi/rooms/{$this->room->public_id}/play/start");
    $this->assertDatabaseHas('room_states', [
        'room_id' => $this->room->id,
        'status' => RoomStatus::STANDBY->value,
    ]);

    $response->assertStatus(201);
});
