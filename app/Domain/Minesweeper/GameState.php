<?php

namespace App\Domain\Minesweeper;

use InvalidArgumentException;

class GameState
{
    private Board $board;

    private int $width;

    private int $height;

    private int $numOfMines;

    private array $visitedTiles;

    private bool $isGameStarted;

    private bool $isGameOver;

    private bool $isGameClear;

    public function __construct(Board $board, int $width, int $height, int $numOfMines)
    {
        $this->board = $board;
        $this->width = $width;
        $this->height = $height;
        $this->numOfMines = $numOfMines;
        $this->visitedTiles = [];
        $this->isGameStarted = false;
        $this->isGameOver = false;
        $this->isGameClear = false;
    }

    public function getBoard(): Board
    {
        return $this->board;
    }

    public function setBoard(Board $board): void
    {
        $this->board = $board;
    }

    public function getGameState(): array
    {
        return $this->board->getBoardState();
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function getNumOfMines(): int
    {
        return $this->numOfMines;
    }

    public function getVisitedTiles(): array
    {
        return $this->visitedTiles;
    }

    public function setVisitedTiles(array $visitedTiles): void
    {
        $this->visitedTiles = $visitedTiles;
    }

    public function isGameStarted(): bool
    {
        return $this->isGameStarted;
    }

    public function isGameOver(): bool
    {
        return $this->isGameOver;
    }

    public function isGameClear(): bool
    {
        return $this->isGameClear;
    }

    // 状態管理用メソッド
    public function startGame(): self
    {
        $this->isGameStarted = true;

        return $this;
    }

    public function endGame(bool $isWin): self
    {
        $this->isGameClear = $isWin;
        $this->isGameOver = true;

        return $this;
    }

    public function addVisitedTile(Tile $tile): self
    {
        $this->visitedTiles["{$tile->x()}-{$tile->y()}"] = true;

        return $this;
    }

    public function isTileVisited(Tile $tile): bool
    {
        return isset($this->visitedTiles["{$tile->x()}-{$tile->y()}"]);
    }

    // シリアライズ関連メソッド
    public function toArray(): array
    {
        $board = $this->board->getBoardState();
        // ボードの2次元配列をシリアライズ可能な形式に変換
        $serializedBoard = [];
        foreach ($board as $y => $row) {
            $serializedBoard[$y] = $row;
            foreach ($row as $x => $tile) {
                $serializedBoard[$y][$x] = $tile->toArray();
            }
        }

        // 訪問済みタイルの位置を記録
        $visitedPositions = array_map(function ($isVisited) {
            return true;
        }, $this->visitedTiles);

        return [
            'tileStates' => $serializedBoard,
            'width' => $this->width,
            'height' => $this->height,
            'numOfMines' => $this->numOfMines,
            'visitedTiles' => count($visitedPositions),
            'isGameStarted' => $this->isGameStarted,
            'isGameOver' => $this->isGameOver,
            'isGameClear' => $this->isGameClear,
        ];
    }

    // クライアント向けの状態（地雷情報はゲームオーバーのみ送信）
    public function toClientArray(): array
    {
        $data = $this->toArray();

        if (! $this->isGameOver()) {
            foreach ($data['tileStates'] as $y => $row) {
                foreach ($row as $x => $tile) {
                    if (! $tile['isOpen']) {
                        $data['tileStates'][$y][$x]['isMine'] = false;
                    }
                }
            }
        }

        return $data;
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function fromArray(array $serialized): GameState
    {

        // 必須フィールドの存在チェック
        $requiredFields = ['width', 'height', 'numOfMines', 'tileStates'];
        foreach ($requiredFields as $field) {
            if (! isset($serialized[$field])) {
                throw new InvalidArgumentException("不完全なゲームデータです: {$field}が欠けています");
            }
        }

        // ボードデータの型チェック
        if (! is_array($serialized['tileStates'])) {
            throw new InvalidArgumentException('ボードデータが無効です');
        }

        // ボードサイズとwith, heightの一致チェック
        $isBoardWidthMatch = count($serialized['tileStates'][0]) === $serialized['width'];
        $isBoardHeightMatch = count($serialized['tileStates']) === $serialized['height'];
        if (! ($isBoardWidthMatch && $isBoardHeightMatch)) {
            throw new InvalidArgumentException('ボードサイズとwidth/heightが一致しません');
        }
        $width = $serialized['width'];
        $height = $serialized['height'];
        $numOfMines = $serialized['numOfMines'];

        $boardInstance = GameService::createBoard($width, $height);
        $restoredBoard = $boardInstance->getBoardState();

        // ボード状態を復元
        foreach ($serialized['tileStates'] as $y => $row) {
            foreach ($row as $x => $tile) {
                $restoredBoard[$y][$x]->setOpen($tile['isOpen']);
                $restoredBoard[$y][$x]->setFlag($tile['isFlag']);
                $restoredBoard[$y][$x]->setMine($tile['isMine']);
                $restoredBoard[$y][$x]->setAdjacentMines($tile['adjacentMines']);
            }
        }

        $restoreState = new self($boardInstance, $width, $height, $numOfMines);

        // ゲーム状態を復元
        $restoreState->isGameStarted = $serialized['isGameStarted'];
        $restoreState->isGameOver = $serialized['isGameOver'];
        $restoreState->isGameClear = $serialized['isGameClear'];

        // 展開されたタイル情報を復元
        foreach ($restoredBoard as $row) {
            foreach ($row as $tile) {
                if ($tile->isOpen()) {
                    $restoreState->addVisitedTile($tile);
                }
            }
        }

        return $restoreState;
    }

    public static function fromPrimitive(array $tileStates, int $width, int $height, int $numOfMines, bool $isGameStarted, bool $isGameClear, bool $isGameOver): GameState
    {
        $boardInstance = GameService::createBoard($width, $height);
        $boardInstance->restoreBoard($tileStates);

        $restoreGameState = new self($boardInstance, $width, $height, $numOfMines);
        $restoreGameState->isGameStarted = $isGameStarted;
        $restoreGameState->isGameClear = $isGameClear;
        $restoreGameState->isGameOver = $isGameOver;
        // 展開されたタイル情報を復元
        foreach ($boardInstance->getBoardState() as $row) {
            foreach ($row as $tile) {
                if ($tile->isOpen()) {
                    $restoreGameState->addVisitedTile($tile);
                }
            }
        }

        return $restoreGameState;
    }
}
