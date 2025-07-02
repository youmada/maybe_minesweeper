<?php

use App\Models\Room;
use App\Models\RoomUser;
use App\Services\Multi\MagicLinkService;

beforeEach(function () {
    $this->room = Room::factory()->create();
    $this->magicLink = $this->room->magic_link_token;

    $this->player = RoomUser::factory()->create([
        'room_id' => $this->room->id,
    ]);
});

it('can verify a magic link from user send', function () {
    expect((new MagicLinkService)->verify($this->room->public_id, $this->room->magic_link_token, $this->player->id))->toBeTrue();
});

it('can not verify a magic link, because of room is not exists.', function () {
    expect((new MagicLinkService)->verify('not-exists', $this->room->magic_link_token, $this->player->id))->toBeFalse();
});

it('can not verify a magic link, because of invalid magic link token', function () {
    expect((new MagicLinkService)->verify($this->room->public_id, 'invalid_token', $this->player->id))->toBeFalse();
});

it('can not verify a magic link, because of magic link expired', function () {
    $this->room->update([
        'expire_at' => now()->subDay(),
    ]);
    $this->room->refresh();
    expect((new MagicLinkService)->verify($this->room->public_id, $this->room->magic_link_token, $this->player->id))->toBeFalse();
});

it("can not verify a magic link, because of room's player limit is over", function () {
    $this->room->update([
        'max_player' => 1,
        'players' => [$this->player->id],
    ]);
    $this->room->refresh();
    $player2 = RoomUser::factory()->create([]);
    expect((new MagicLinkService)->verify($this->room->public_id, $this->room->magic_link_token, $player2->id))->toBeFalse();
});

it("can verify a magic link, because of room's player limit is over but player is already registered.", function () {
    $this->room->update([
        'max_player' => 1,
        'players' => [$this->player->id],
    ]);
    $this->room->refresh();
    expect((new MagicLinkService)->verify($this->room->public_id, $this->room->magic_link_token, $this->player->id))->toBeTrue();

});
