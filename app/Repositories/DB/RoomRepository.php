<?php

namespace App\Repositories\DB;

use App\Domain\Room\Room as RoomDomain;
use App\Domain\Room\RoomAggregate;
use App\Domain\Room\RoomState as RoomStateDomain;
use App\Exceptions\RoomException;
use App\Models\Room;
use App\Models\RoomState;
use App\Models\RoomUser;
use App\Repositories\Interfaces\RoomRepositoryInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RoomRepository implements RoomRepositoryInterface
{
    /**
     * @throws RoomException
     */
    public function save(RoomAggregate $roomAggregate, string $roomId): void
    {
        // すでに登録されている場合はスキップ
        if (Room::where('id', $roomId)->exists()) {
            return;
        }

        $toArrayRoom = $roomAggregate->getRoom()->toArray();
        $toArrayRoomState = $roomAggregate->getRoomState()->toArray();

        $mappedRoom = $this->getMappedRoom($toArrayRoom);

        $mappedRoomState = $this->getMappedRoomState($toArrayRoomState, $roomId);

        $magicLinkToken = $this->generateUniqueToken();

        try {
            Room::create($mappedRoom + ['magic_link_token' => $magicLinkToken]);
            RoomState::create($mappedRoomState);
            RoomUser::create([
                'room_id' => $roomId,
                'user_id' => $toArrayRoom['ownerId'],
                'joined_at' => Carbon::now()->toDateTimeString(),
            ]);
        } catch (RoomException $e) {
            Log::error("DB save method error for key {$roomId}: ".$e->getMessage());
            throw $e;
        }
    }

    /**
     * @throws RoomException
     */
    public function get(string $roomId): RoomAggregate
    {
        try {
            $this->checkRoomAndStateExists($roomId);
            $room = Room::where('id', $roomId)->first();
            $roomState = RoomState::where('room_id', $roomId)->first();

            $roomAggregate = new RoomAggregate(
                RoomDomain::fromArray($room->toArrayWithMagicLink()),
                RoomStateDomain::fromArray($roomState->toArray())
            );
        } catch (RoomException $e) {
            Log::error("DB get method error for key {$roomId}: ".$e->getMessage());
            throw $e;
        }

        return $roomAggregate;
    }

    /**
     * @throws RoomException
     */
    public function update(RoomAggregate $roomAggregate, string $roomId): void
    {
        $addPlayers = $roomAggregate->getPlayers();
        $currentRoomPlayers = Room::where('id', $roomId)->first()->players ?? [];
        try {
            $this->checkRoomAndStateExists($roomId);

            $roomData = $roomAggregate->getRoom()->toArray();
            $roomStateData = $roomAggregate->getRoomState()->toArray();

            Room::where('id', $roomId)->update($this->getMappedRoom($roomData));
            RoomState::where('room_id', $roomId)->update($this->getMappedRoomState($roomStateData, $roomId));
            foreach ($addPlayers as $player) {
                if (! in_array($player, $currentRoomPlayers, true)) {
                    RoomUser::create([
                        'room_id' => $roomId,
                        'user_id' => $player,
                        'joined_at' => now(),
                    ]);
                }
            }
            // room_usersテーブル
        } catch (RoomException $e) {
            Log::error("DB update method error for key {$roomId}: ".$e->getMessage());
            throw $e;
        }
    }

    /**
     * @throws RoomException
     */
    public function delete(string $roomId): void
    {
        try {
            $this->checkRoomAndStateExists($roomId);

            Room::where('id', $roomId)->delete();
        } catch (RoomException $e) {
            Log::error("DB delete method error for key {$roomId}: ".$e->getMessage());
            throw $e;
        }

    }

    private function getMappedRoom(array $toArrayRoom): array
    {
        return [
            'name' => $toArrayRoom['name'],
            'max_player' => $toArrayRoom['maxPlayer'],
            'is_private' => $toArrayRoom['isPrivate'],
            'owner_id' => $toArrayRoom['ownerId'],
            'players' => $toArrayRoom['players'],
        ];
    }

    private function getMappedRoomState(array $toArrayRoomState, string $roomId): array
    {
        return [
            'turn_order' => $toArrayRoomState['turnOrder'],
            'status' => $toArrayRoomState['status'],
            'flag_limit' => $toArrayRoomState['flagLimit'],
            'room_id' => $roomId,
        ];
    }

    private function checkRoomAndStateExists(string $roomId): void
    {
        if (! Room::where('id', $roomId)->exists()) {
            throw new RoomException('Room not found');
        }

        if (! RoomState::where('room_id', $roomId)->exists()) {
            throw new RoomException('RoomState not found');
        }
    }

    // トークンの一意性を保証するヘルパー
    private function generateUniqueToken(): string
    {
        do {
            $token = Str::random(32);
        } while (Room::where('magic_link_token', $token)->exists());

        return $token;
    }
}
