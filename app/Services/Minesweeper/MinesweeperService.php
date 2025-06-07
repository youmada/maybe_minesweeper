<?php

namespace App\Services\Minesweeper;

use App\Domain\Minesweeper\GameService;
use App\Domain\Minesweeper\GameState;
use App\Domain\Minesweeper\TileActionMode;
use App\Repositories\Interfaces\GameRepositoryInterface;

class MinesweeperService
{
    private GameState $gameState;

    private const MIN_SAFE_TILES_AROUND_START = 9;

    private string $game_uuid;

    private GameService $gameService;

    private GameRepositoryInterface $repository;
    // 必要な処理一覧
    // 1. ゲーム初期化 ✅
    // 2. タイルクリック時の処理 ✅
    // 3. データをリポジトリクラスに橋渡し
    // 4. クライアントサイドへのデータ加工と出力 ✅
    // 5. ゲームクリア・オーバー時の処理 ✅

    public function __construct(GameService $gameService, GameRepositoryInterface $repository)
    {
        $this->gameService = $gameService;
        $this->repository = $repository;
        $this->game_uuid = self::createUUID();
    }

    /**
     * @param  int  $width  // 幅
     * @param  int  $height  // 高さ
     * @param  int  $numOfMines  // 地雷数
     *                           ゲーム開始処理（続きからプレイでは使わないことを想定している。）
     */
    public function initializeGame(int $width, int $height, int $numOfMines): self
    {

        // ボードを生成する
        $board = $this->gameService::createBoard($width, $height);

        // 地雷数の計算 (mineRatioはパーセンテージ)
        $totalTiles = $width * $height;
        // 最小値と最大値の制限
        $numOfMines = max(1, min($numOfMines, $totalTiles - $this::MIN_SAFE_TILES_AROUND_START));

        $this->gameState = new GameState($board, $width, $height, $numOfMines);

        // リポジトリ層に保存
        $this->repository->saveState($this->gameState, $this->game_uuid);

        return $this;
    }

    /**
     * @throws \Exception
     */
    public function continueGame(string $gameId): GameState
    {
        $state = $this->repository->getState($gameId);
        if (! $state) {
            throw new \Exception("Game not found: {$gameId}");
        }

        return $state;
    }

    protected function setMinesOnTheBoard(int $firstClickPosX, int $firstClickPosY): void
    {
        $board = $this->gameState->getBoard();
        $numOfMines = $this->gameState->getNumOfMines();
        $firstClickPos = [
            'x' => $firstClickPosX,
            'y' => $firstClickPosY,
        ];
        $this->gameService::setMines($board, $numOfMines, $firstClickPos);
    }

    public function getGameStateForClient(): array
    {
        return $this->gameState->toClientArray();
    }

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
                // TODO: ここにリポジトリクラスが必要
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

        // TODO: ここにリポジトリクラスが必要
        return $this->gameState;
    }

    /*
     * 初回クリック時の処理
     */
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

    private static function createUUID(): string
    {
        return uuid_create(UUID_TYPE_RANDOM);
    }

    public function getGameId(): string
    {
        return $this->game_uuid;
    }
}
