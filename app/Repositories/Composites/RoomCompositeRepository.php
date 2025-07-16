<?php

namespace App\Repositories\Composites;

use App\Domain\Room\RoomAggregate;
use App\Domain\Room\RoomStatus;
use App\Repositories\DB\RoomRepository as DBRepository;
use App\Repositories\Interfaces\RoomCompositesRepositoryInterface;
use App\Repositories\Redis\RoomRepository as RedisRepository;
use Illuminate\Support\Facades\Log;

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
        //        Log::info($roomState->getStatus());
        //        Log::info($roomState->getStatus() === RoomStatus::PLAYING->value ? 'redis get' : 'db get');
        if ($roomAggregate->getRoomStatus() === RoomStatus::PLAYING->value) {
            \Illuminate\Support\Facades\Log::info('redis get');

            return $roomAggregate;
        } else {
            \Illuminate\Support\Facades\Log::info('db get');

            return $this->dbRepo->get($roomId);
        }
    }

    /**
     * @throws \Exception
     */
    public function update(RoomAggregate $roomAggregate, string $roomId): void
    {
        // プレイ中は高速に Redis、終了時は DB へも upsert
        // RoomStateを渡されるのはredisでのリポジトリアクセス
        // redisでのアクセスはRoomStatus::PLAYINGなので下記条件分岐で問題ない
        \Illuminate\Support\Facades\Log::info('redis update');
        $this->redisRepo->update($roomAggregate, $roomId);
        \Illuminate\Support\Facades\Log::info('db update');
        if ($roomAggregate->getRoomStatus() === RoomStatus::PLAYING->value) {
            return;
        }
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
