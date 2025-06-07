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

    public function saveState(GameState $state, string $gameId): void
    {
        // 初回は DB に設計情報だけ書き
        $this->dbRepo->saveState($state, $gameId);
        // その後、必ず Redis にも書く
        $this->redisRepo->saveState($state, $gameId);
    }

    public function getState(string $gameId): ?GameState
    {
        // まず Redis を優先して読んで、なければ DB までフォールバック
        return $this->redisRepo->getState($gameId)
        ?? $this->dbRepo->getState($gameId);
    }

    public function updateState(GameState $state, string $gameId): void
    {
        // TODO: DBに保存するのは、ゲーム中断時なのでroom状態を取得して、条件分岐させる。
        // プレイ中は高速に Redis、終了時は DB へも upsert
        $this->redisRepo->updateState($state, $gameId);
        if ($state->isGameOver() || $state->isGameClear()) {
            $this->dbRepo->updateState($state, $gameId);
        }
    }

    public function deleteState(string $gameId): void
    {
        $this->redisRepo->deleteState($gameId);
        $this->dbRepo->deleteState($gameId);
    }
}
