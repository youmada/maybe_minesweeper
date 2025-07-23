<?php

use App\Domain\Room\RoomStatus;
use App\Events\RoomStatusApplyClient;
use App\Models\Player;
use App\Models\Room;
use App\Services\Multi\CreateRoomService;
use Carbon\Carbon;

beforeEach(function () {
    $this->player = Player::factory()->create();
    $roomId = app(CreateRoomService::class)(
        roomName: 'test room',
        maxPlayers: 2,
        ownerId: $this->player->public_id,
        expireAt: Carbon::now()->addDay(),
        isPrivate: true,
        players: [$this->player->public_id],
        flagLimit: 5);
    $this->room = Room::find($roomId);
});

it('should start game play', function () {
    Event::fake();
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

it('should dispatch a status apply event', function () {
    Event::fake();
    $this->actingAs($this->player, 'magicLink')
        ->withSession(['public_id' => $this->player->public_id])
        ->post("multi/rooms/{$this->room->public_id}/play/start");

    Event::assertDispatched(RoomStatusApplyClient::class);

});
