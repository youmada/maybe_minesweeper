<?php

namespace App\Services\Minesweeper;

use App\Domain\Minesweeper\GameService;
use App\Domain\Minesweeper\GameState;
use App\Domain\Minesweeper\TileActionMode;
use App\Repositories\Interfaces\GameRepositoryInterface;
use Exception;

class MinesweeperService
{
    private const MIN_SAFE_TILES_AROUND_START = 9;

    private GameRepositoryInterface $repository;
    // 必要な処理一覧
    // 1. ゲーム初期化 ✅
    // 2. タイルクリック時の処理 ✅
    // 3. データをリポジトリクラスに橋渡し
    // 4. クライアントサイドへのデータ加工と出力 ✅
    // 5. ゲームクリア・オーバー時の処理 ✅

    public function __construct(GameRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param  int  $width  // 幅
     * @param  int  $height  // 高さ
     * @param  int  $numOfMines  // 地雷数
     * @param  string  $roomId
     *                          ゲーム開始処理（続きからプレイでは使わないことを想定している。）
     */
    public function initializeGame(string $roomId, int $width, int $height, int $numOfMines): GameState
    {

        // ボードを生成する
        $board = gameService::createBoard($width, $height);

        // 地雷数の計算 (mineRatioはパーセンテージ)
        $totalTiles = $width * $height;
        // 最小値と最大値の制限
        $numOfMines = max(1, min($numOfMines, $totalTiles - $this::MIN_SAFE_TILES_AROUND_START));

        $gameState = new GameState($board, $width, $height, $numOfMines);

        // リポジトリ層に保存
        $this->repository->saveState($gameState, $roomId);

        return $gameState;
    }

    /**
     * @throws Exception
     */
    public function continueGame(string $roomId): GameState
    {
        $state = $this->repository->getState($roomId);
        if (! $state) {
            throw new Exception("Game not found: {$roomId}");
        }

        return $state;
    }

    protected function setMinesOnTheBoard(int $firstClickPosX, int $firstClickPosY, GameState $gameState): GameState
    {
        $board = $gameState->getBoard();
        $numOfMines = $gameState->getNumOfMines();
        $firstClickPos = [
            'x' => $firstClickPosX,
            'y' => $firstClickPosY,
        ];
        GameService::setMines($board, $numOfMines, $firstClickPos);

        return $gameState;
    }

    public function getGameStateForClient(GameState $gameState): array
    {
        return $gameState->toClientArray();
    }

    // どんな処理が必要？
    // gameStateへの反映 ✅
    // ゲームオーバー・クリアのチェック ✅
    // リポジトリへの反映
    // 状態の返却 ✅
    /**
     * @throws Exception
     */
    public function handleClickTile(string $roomId, int $clickTileX, int $clickTileY, TileActionMode $mode): GameState
    {
        // リポジトリから現在の状態をロード
        $state = $this->repository->getState($roomId) ?? throw new Exception("Game not found: {$roomId}");

        $board = $state->getBoard();
        $visitedTiles = $state->getVisitedTiles();
        $currentClickTile = GameService::getTile($board, $clickTileX, $clickTileY);
        $totalMines = $state->getNumOfMines();

        if ($mode === TileActionMode::OPEN) {
            if (! $currentClickTile->isFlag()) {
                // タイルを開く
                GameService::openTile($board, $currentClickTile, $visitedTiles);
                // タイルが地雷かチェックする
                if (GameService::checkGameOver($currentClickTile)) {
                    $state->endGame(false);
                } elseif (GameService::checkGameClear($board, $totalMines)) {
                    $state->endGame(true);
                }
            }
        } else {
            GameService::toggleFlag($currentClickTile);
        }
        // 更新した状態を永続化
        $this->repository->updateState($state, $roomId);

        return $state;
    }

    /*
     * 初回クリック時の処理
     */
    /**
     * @throws Exception
     */
    public function processGameStart(string $roomId, int $firstClickPosX, int $firstClickPosY): GameState
    {
        $state = $this->repository->getState($roomId) ?? throw new Exception("Game not found: {$roomId}");
        $state->startGame();
        $gameState = $this->setMinesOnTheBoard($firstClickPosX, $firstClickPosY, $state);
        $this->repository->updateState($gameState, $roomId);

        // 初回クリック操作

        return $this->handleClickTile($roomId, $firstClickPosX, $firstClickPosY, TileActionMode::OPEN);
    }
}
