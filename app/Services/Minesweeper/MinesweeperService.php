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
        // 再生成のためのデータを取得する
        $oldGameState = $this->repository->getState($roomId) ?? throw new Exception("Game not found: {$roomId}");
        // 現在のゲームデータを削除
        $this->repository->deleteState($roomId);

        // 現在のwidthなどの情報で再生成
        return $this->initializeGame($roomId, $oldGameState->getWidth(), $oldGameState->getHeight(), $oldGameState->getNumOfMines());
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

    public function getGameStateForClient(GameState $gameState, ?GameState $previousState = null): array
    {
        $current = $gameState->toClientArray();

        if (is_null($previousState)) {
            // 初期化時や旧状態がない場合は全体送信
            return $current;
        }

        $previous = $previousState->toClientArray();

        // 差分抽出
        $diff = [];

        foreach ($current['tileStates'] as $y => $row) {
            foreach ($row as $x => $tile) {
                if ($previous['tileStates'][$y][$x] !== $tile) {
                    $diff['tileStates'][$y][$x] = $tile;
                }
            }
        }

        // その他必要なデータも含めて返す
        return [
            ...$diff, // キー名tileStatesが入る
            'width' => $current['width'],
            'height' => $current['height'],
            'numOfMines' => $current['numOfMines'],
            'visitedTiles' => $current['visitedTiles'],
            'isGameStarted' => $current['isGameStarted'],
            'isGameOver' => $current['isGameOver'],
            'isGameClear' => $current['isGameClear'],
        ];
    }

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
                $state->setVisitedTiles($visitedTiles);
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
