<?php

namespace App\Repositories\DB;

use App\Domain\Room\Room as RoomDomain;
use App\Domain\Room\RoomAggregate;
use App\Domain\Room\RoomState as RoomStateDomain;
use App\Exceptions\RoomException;
use App\Models\Room;
use App\Models\RoomState;
use App\Repositories\Interfaces\RoomRepositoryInterface;
use Illuminate\Support\Facades\Log;

class RoomRepository implements RoomRepositoryInterface
{
    /**
     * @throws RoomException
     */
    public function save(RoomAggregate $roomAggregate, string $roomId): void
    {
        if (Room::where('room_id', $roomId)->exists()) {
            return;
        }
        $toArrayRoom = $roomAggregate->getRoom()->toArray();
        $toArrayRoomState = $roomAggregate->getRoomState()->toArray();

        $mappedRoom = $this->getMappedRoom($toArrayRoom);

        $mappedRoomState = $this->getMappedRoomState($toArrayRoomState);
        try {
            Room::create($mappedRoom);
            RoomState::create($mappedRoomState);
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
            $room = Room::where('room_id', $roomId)->first();
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
        try {
            $this->checkRoomAndStateExists($roomId);

            $roomData = $roomAggregate->getRoom()->toArray();
            $roomStateData = $roomAggregate->getRoomState()->toArray();

            Room::where('room_id', $roomId)->update($this->getMappedRoom($roomData));
            RoomState::where('room_id', $roomId)->update($this->getMappedRoomState($roomStateData));
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

            Room::where('room_id', $roomId)->delete();
            RoomState::where('room_id', $roomId)->delete();
        } catch (RoomException $e) {
            Log::error("DB delete method error for key {$roomId}: ".$e->getMessage());
            throw $e;
        }

    }

    private function getMappedRoom(array $toArrayRoom): array
    {
        return [
            'room_id' => $toArrayRoom['roomId'],
            'name' => $toArrayRoom['name'],
            'max_player' => $toArrayRoom['maxPlayer'],
            'magic_link_token' => $toArrayRoom['magicLinkToken'],
            'is_private' => $toArrayRoom['isPrivate'],
            'owner_id' => $toArrayRoom['ownerId'],
            'players' => $toArrayRoom['players'],
        ];
    }

    private function getMappedRoomState(array $toArrayRoomState): array
    {
        return [
            'room_id' => $toArrayRoomState['roomId'],
            'turn_order' => $toArrayRoomState['turnOrder'],
            'status' => $toArrayRoomState['status'],
            'flag_limit' => $toArrayRoomState['flagLimit'],
        ];
    }

    private function checkRoomAndStateExists(string $roomId): void
    {
        if (! Room::where('room_id', $roomId)->exists()) {
            throw new RoomException('Room not found');
        }

        if (! RoomState::where('room_id', $roomId)->exists()) {
            throw new RoomException('RoomState not found');
        }
    }
}
