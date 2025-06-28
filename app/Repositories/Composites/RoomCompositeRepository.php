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
    public function save(RoomAggregate $roomAggregate, string $roomId): void
    {
        // 初回は DB に設計情報だけ書き
        $this->dbRepo->save($roomAggregate, $roomId);
        // その後、必ず Redis にも書く
        $this->redisRepo->save($roomAggregate->getRoomState(), $roomId);
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
    public function update(RoomAggregate $roomAggregate, string $roomId): void
    {
        // TODO: DBに保存するのは、ゲーム中断時なのでroom状態を取得して、条件分岐させる。
        // プレイ中は高速に Redis、終了時は DB へも upsert
        $this->redisRepo->update($roomAggregate->getRoomState(), $roomId);
        $this->dbRepo->update($roomAggregate, $roomId);
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
