<?php

use App\Events\RoomStateApplyClientEvent;
use App\Models\Player;
use App\Models\Room;
use App\Repositories\Composites\RoomCompositeRepository;
use App\Services\Multi\CreateRoomService;
use App\Services\Multi\JoinRoomService;
use App\Utils\UUIDFactory;
use Illuminate\Support\Carbon;

use function App\Routes\isRoomExists;

beforeEach(function () {
    $this->player = Player::factory()->create();
    $this->player2 = Player::factory()->create();

    $this->roomId = app(CreateRoomService::class)(
        roomName: 'test room',
        maxPlayers: 2,
        ownerId: $this->player->public_id,
        expireAt: Carbon::now()->addDay(),
        isPrivate: true,
        players: [$this->player->public_id],
        flagLimit: 5);
    app(JoinRoomService::class)($this->roomId, $this->player->public_id);
    $this->room = Room::find($this->roomId);
    $this->roomRepository = app(RoomCompositeRepository::class)->get($this->room->id);
});
it('should return room state data payload', function () {

    $event = new RoomStateApplyClientEvent($this->room);

    expect($event->broadcastWith())->toBe(
        ['data' => [
            'currentPlayer' => $this->roomRepository->getCurrentOrder(),
            'turnActionState' => [
                'tileOpened' => $this->roomRepository->getActionState()['tileOpened'],
                'flagCount' => $this->roomRepository->getActionState()['flagCount'],
                'flagLimit' => $this->roomRepository->getFlagLimit(),
            ],
        ]]
    );
});

it('checks if the player belongs to the room', function () {
    $player = Player::factory()->create();
    $room = Room::factory()->create();

    // プレイヤーとルームを関連づける
    $player->rooms()->attach($room);

    // 関数の動作を検証
    expect(isRoomExists($player, $room->public_id))->toBeTrue();
    expect(isRoomExists($player, UUIDFactory::generate()))->toBeFalse();
    expect(isRoomExists($this->player2, $room->public_id))->toBeFalse();
});
