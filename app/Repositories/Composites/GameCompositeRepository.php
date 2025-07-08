<?php

namespace App\Repositories\Composites;

use App\Domain\Minesweeper\GameState;
use App\Repositories\DB\MinesweeperRepository as DBRepo;
use App\Repositories\Interfaces\GameRepositoryInterface;
use App\Repositories\Redis\MinesweeperRepository as RedisRepo;

class GameCompositeRepository implements GameRepositoryInterface
{
    public function __construct(
        private RedisRepo $redisRepo,
        private DBRepo $dbRepo
    ) {}

    public function saveState(GameState $state, string $roomId): void
    {
        // 初回は DB に設計情報だけ書き
        $this->dbRepo->saveState($state, $roomId);
        // その後、必ず Redis にも書く
        $this->redisRepo->saveState($state, $roomId);
    }

    public function getState(string $roomId): ?GameState
    {
        // まず Redis を優先して読んで、なければ DB までフォールバック
        //        return $this->redisRepo->getState($roomId)
        //        ?? $this->dbRepo->getState($roomId);
        $state = $this->redisRepo->getState($roomId);

        if ($state !== null) {
            logger()->info('Redis から取得しました');

            return $state;
        }

        $state = $this->dbRepo->getState($roomId);

        if ($state !== null) {
            logger()->info('DB から取得しました');
        } else {
            logger()->warning('状態が Redis にも DB にも存在しません');
        }

        return $state;
    }

    public function updateState(GameState $state, string $roomId): void
    {
        // TODO: DBに保存するのは、ゲーム中断時なのでroom状態を取得して、条件分岐させる。
        // プレイ中は高速に Redis、終了時は DB へも upsert
        $this->redisRepo->updateState($state, $roomId);
        if ($state->isGameOver() || $state->isGameClear()) {
            $this->dbRepo->updateState($state, $roomId);
        }
    }

    public function deleteState(string $roomId): void
    {
        $this->redisRepo->deleteState($roomId);
        $this->dbRepo->deleteState($roomId);
    }
}
