<?php

namespace App\Repositories\Composites;

use App\Domain\Room\RoomAggregate;
use App\Domain\Room\RoomState;
use App\Domain\Room\RoomStatus;
use App\Repositories\DB\RoomRepository as DBRepository;
use App\Repositories\Interfaces\RoomCompositesRepositoryInterface;
use App\Repositories\Redis\RoomRepository as RedisRepository;

class RoomCompositeRepository implements RoomCompositesRepositoryInterface
{
    public function __construct(
        private readonly RedisRepository $redisRepo,
        private readonly DBRepository $dbRepo
    ) {}

    /**
     * @throws \Exception
     */
    public function create(RoomAggregate $roomAggregate): string
    {
        // 初回は DB に設計情報だけ書き
        $roomId = $this->dbRepo->create($roomAggregate);
        // その後、必ず Redis にも書く
        $this->redisRepo->save($roomAggregate->getRoomState(), $roomId);

        return $roomId;
    }

    public function get(string $roomId): RoomAggregate|RoomState|null
    {
        $roomSate = $this->redisRepo->get($roomId);
        if ($roomSate->getStatus() === RoomStatus::PLAYING->value) {
            return $roomSate;
        } else {
            return $this->dbRepo->get($roomId);
        }
    }

    /**
     * @throws \Exception
     */
    public function update(RoomAggregate|RoomState $room, string $roomId): void
    {
        // プレイ中は高速に Redis、終了時は DB へも upsert
        // RoomStateを渡されるのはredisでのリポジトリアクセス
        // redisでのアクセスはRoomStatus::PLAYINGなので下記条件分岐で問題ない
        if ($room instanceof RoomState) {
            $this->redisRepo->update($room, $roomId);

            return;
        }
        $this->dbRepo->update($room, $roomId);
    }

    /**
     * @throws \Exception
     */
    public function delete(string $roomId): void
    {
        $this->redisRepo->delete($roomId);
        $this->dbRepo->delete($roomId);
    }
}
