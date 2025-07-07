<?php

namespace App\Repositories\DB;

use App\Domain\Room\Room as RoomDomain;
use App\Domain\Room\RoomAggregate;
use App\Domain\Room\RoomState as RoomStateDomain;
use App\Exceptions\RoomException;
use App\Models\Player;
use App\Models\Room;
use App\Models\RoomState;
use App\Repositories\Interfaces\RoomRepositoryInterface;
use App\Utils\UUIDFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RoomRepository implements RoomRepositoryInterface
{
    /**
     * createメソッドは必ず各ルーム作成で一度だけ呼び出す。
     *
     * @throws RoomException
     */
    public function create(RoomAggregate $roomAggregate): ?string
    {

        $publicRoomId = UUIDFactory::generate();
        $toArrayRoom = $roomAggregate->getRoom()->toArray();
        $toArrayRoomState = $roomAggregate->getRoomState()->toArray();

        $mappedRoom = $this->getMappedRoom($toArrayRoom, $publicRoomId);

        $magicLinkToken = $this->generateUniqueToken();

        try {
            $room = DB::transaction(function () use ($mappedRoom, $toArrayRoomState, $toArrayRoom, $magicLinkToken) {
                $player = Player::firstOrCreate([
                    'session_id' => $toArrayRoom['ownerId'],
                ]);
                $room = Room::create($mappedRoom + ['magic_link_token' => $magicLinkToken, 'owner_id' => $player->id]);
                RoomState::create($this->getMappedRoomState($toArrayRoomState, $room->id));

                $room->players()->attach($player->id, [
                    'joined_at' => now(),
                    'left_at' => null,
                ]);

                return $room;
            });

            return $room->id;

        } catch (RoomException $e) {
            Log::error("DB save method error for public room id {$publicRoomId}: ".$e->getMessage());
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
        $room = Room::where('id', $roomId)->first();
        $addPlayerIds = $roomAggregate->getPlayers();
        //        $currentRoomPlayers = $room->players ?? [];
        try {
            $this->checkRoomAndStateExists($roomId);

            $roomData = $roomAggregate->getRoom()->toArray();
            $roomStateData = $roomAggregate->getRoomState()->toArray();

            Room::where('id', $roomId)->update($this->getMappedRoom($roomData));
            RoomState::where('room_id', $roomId)->update($this->getMappedRoomState($roomStateData, $roomId));

            $room->players()->detach();
            foreach ($addPlayerIds as $playerId) {
                $player = Player::firstOrCreate([
                    'session_id' => $playerId,
                ]);
                $room->players()->attach($player->id, [
                    'joined_at' => now(),
                    'left_at' => null,
                ]);
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

    private function getMappedRoom(array $toArrayRoom, ?string $publicRoomId = null): array
    {
        $mapped = [
            'name' => $toArrayRoom['name'],
            'max_player' => $toArrayRoom['maxPlayer'],
            'is_private' => $toArrayRoom['isPrivate'],
            'expire_at' => $toArrayRoom['expireAt'],
        ];

        if (! is_null($publicRoomId)) {
            $mapped['public_id'] = $publicRoomId;
        }

        return $mapped;
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
