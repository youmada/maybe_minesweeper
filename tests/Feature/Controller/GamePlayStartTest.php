<?php

use App\Domain\Room\RoomStatus;
use App\Models\Player;
use App\Models\Room;
use App\Models\RoomState;
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
        ->withSession(['player_id' => $this->player->session_id])
        ->post("multi/rooms/{$this->room->public_id}/play/start");
    $this->assertDatabaseHas('room_states', [
        'room_id' => $this->room->id,
        'status' => RoomStatus::STANDBY->value,
    ]);

    $response->assertStatus(201);
});
