<?php

use App\Models\Player;
use App\Models\Room;
use App\Services\Multi\MagicLinkService;

beforeEach(function () {
    $this->room = Room::factory()->create();
    $this->magicLink = $this->room->magic_link_token;

    $this->player = Player::factory()->create([]);
});

it('can verify a magic link from user send', function () {
    expect((new MagicLinkService)->verify($this->room->id, $this->room->magic_link_token, $this->player->session_id))->toBeTrue();
});

it('can not verify a magic link, because of room is not exists.', function () {
    expect((new MagicLinkService)->verify('not-exists', $this->room->magic_link_token, $this->player->session_id))->toBeFalse();
});

it('can not verify a magic link, because of invalid magic link token', function () {
    expect((new MagicLinkService)->verify($this->room->id, 'invalid_token', $this->player->session_id))->toBeFalse();
});

it('can not verify a magic link, because of magic link expired', function () {
    $this->room->update([
        'expire_at' => now()->subDay(),
    ]);
    $this->room->refresh();
    expect((new MagicLinkService)->verify($this->room->id, $this->room->magic_link_token, $this->player->session_id))->toBeFalse();
});

it("can not verify a magic link, because of room's player limit is over", function () {
    $this->room->update([
        'max_player' => 1,
    ]);
    $this->room->players()->attach($this->player->id, [
        'joined_at' => now(),
        'left_at' => null,
    ]);
    $this->room->refresh();
    $player2 = Player::factory()->create([]);
    expect((new MagicLinkService)->verify($this->room->id, $this->room->magic_link_token, $player2->session_id))->toBeFalse();
});

it("can verify a magic link, because of room's player limit is over but player is already registered.", function () {
    $this->room->update([
        'max_player' => 1,
    ]);
    $this->room->players()->attach($this->player->id, [
        'joined_at' => now(),
        'left_at' => null,
    ]);
    $this->room->refresh();
    expect((new MagicLinkService)->verify($this->room->id, $this->room->magic_link_token, $this->player->session_id))->toBeTrue();

});
