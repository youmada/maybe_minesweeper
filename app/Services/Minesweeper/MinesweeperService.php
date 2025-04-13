<?php

namespace App\Services\Minesweeper;

use App\Domain\Minesweeper\GameService;
use App\Domain\Minesweeper\GameState;
use App\Domain\Minesweeper\TileActionMode;

class MinesweeperService
{
    private GameState $gameState;

    private GameService $gameService;
    // 必要な処理一覧
    // 1. ゲーム初期化 ✅
    // 2. タイルクリック時の処理 ✅
    // 3. フラグ数を取得する処理
    // 4. データをリポジトリクラスに橋渡し
    // 5. クライアントサイドへのデータ加工と出力
    // 6. ゲームクリア・オーバー時の処理

    public function __construct(GameService $gameService)
    {
        $this->gameService = $gameService;
    }

    /**
     * @param  int  $width  // 幅
     * @param  int  $height  // 高さ
     * @param  int  $mineRatio  // 地雷の割合 パーセンテージ（0 - 100)
     */
    public function initializeGame(int $width, int $height, int $mineRatio): self
    {
        // ボードを生成する
        $board = $this->gameService::createBoard($width, $height);

        // 地雷数の計算 (mineRatioはパーセンテージ)
        $totalTiles = $width * $height;
        $numOfMines = (int) ceil($totalTiles * $mineRatio / 100);
        // 最小値と最大値の制限
        $numOfMines = max(1, min($numOfMines, $totalTiles - 9));

        $this->gameState = new GameState($board, $width, $height, $numOfMines);

        return $this;
    }

    public function setMinesOnTheBoard(int $firstClickPosX, int $firstClickPosY): void
    {
        $board = $this->gameState->getBoard();
        $numOfMines = $this->gameState->getNumOfMines();
        $firstClickPos = [
            'x' => $firstClickPosX,
            'y' => $firstClickPosY,
        ];
        $this->gameService::setMines($board, $numOfMines, $firstClickPos);
    }

    //    private function getGameStateForClient(): array
    //    {
    //        return [];
    //    }

    public function getGameState(): GameState
    {
        return $this->gameState;
    }

    // どんな処理が必要？
    // gameStateへの反映 ✅
    // ゲームオーバー・クリアのチェック ✅
    // リポジトリへの反映
    // 状態の返却 ✅
    public function handleClickTile(int $clickTileX, int $clickTileY, TileActionMode $mode): GameState
    {
        $board = $this->gameState->getBoard();
        $visitedTiles = $this->gameState->getVisitedTiles();
        $currentClickTile = GameService::getTile($board, $clickTileX, $clickTileY);
        $totalMines = $this->gameState->getNumOfMines();

        if ($mode === TileActionMode::OPEN) {
            if ($currentClickTile->isFlag()) {
                return $this->gameState;
            }
            // タイルを開く
            GameService::openTile($board, $currentClickTile, $visitedTiles);
            // タイルが地雷かチェックする
            if (GameService::checkGameOver($currentClickTile)) {
                $this->processGameOver();
            }
            if (GameService::checkGameClear($board, $totalMines)) {
                $this->processGameClear();
            }
        } elseif ($mode === TileActionMode::FLAG) {
            GameService::toggleFlag($currentClickTile);
        }

        return $this->gameState;
    }

    public function processGameStart(int $firstClickPosX, int $firstClickPosY): void
    {
        $this->gameState->startGame();
        $this->setMinesOnTheBoard($firstClickPosX, $firstClickPosY);
        // 初回クリック操作
        $this->handleClickTile($firstClickPosX, $firstClickPosY, TileActionMode::OPEN);
    }

    public function processGameOver(): void
    {
        $this->gameState->endGame(false);
    }

    public function processGameClear(): void
    {
        // ゲームをクリアする
        $this->gameState->endGame(true);
    }
}
