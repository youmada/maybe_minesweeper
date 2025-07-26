<?php

namespace App\Repositories\Composites;

use App\Domain\Room\RoomAggregate;
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
        $this->redisRepo->save($roomAggregate, $roomId);

        return $roomId;
    }

    public function get(string $roomId): ?RoomAggregate
    {
        $roomAggregate = $this->redisRepo->get($roomId);
        if ($roomAggregate->getRoomStatus() === RoomStatus::PLAYING->value) {
            return $roomAggregate;
        } else {
            return $this->dbRepo->get($roomId);
        }
    }

    /**
     * @throws \Exception
     */
    public function update(RoomAggregate $roomAggregate, string $roomId): void
    {
        // プレイ中は高速に Redis、終了時は DB へも upsert
        $this->redisRepo->update($roomAggregate, $roomId);
        $this->dbRepo->update($roomAggregate, $roomId);
        //        if ($roomAggregate->getRoomStatus() === RoomStatus::PLAYING->value) {
        //            return;
        //        }
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
