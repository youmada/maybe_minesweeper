<?php

namespace App\Domain\Minesweeper;

use InvalidArgumentException;
use SplObjectStorage;

class GameState
{
    private Board $board;

    private int $width;

    private int $height;

    private int $numOfMines;

    private SplObjectStorage $visitedTiles;

    private bool $isGameStarted;

    private bool $isGameOver;

    private bool $isGameClear;

    public function __construct(Board $board, int $width, int $height, int $numOfMines)
    {
        $this->board = $board;
        $this->width = $width;
        $this->height = $height;
        $this->numOfMines = $numOfMines;
        $this->visitedTiles = new SplObjectStorage;
        $this->isGameStarted = false;
        $this->isGameOver = false;
        $this->isGameClear = false;
    }

    public function getBoard(): Board
    {
        return $this->board;
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

    public function getVisitedTiles(): SplObjectStorage
    {
        return $this->visitedTiles;
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
        $this->visitedTiles->attach($tile);

        return $this;
    }

    public function isTileVisited(Tile $tile): bool
    {
        return $this->visitedTiles->contains($tile);
    }

    // シリアライズ関連メソッド
    public function toArray(): array
    {
        // ボードの2次元配列をシリアライズ可能な形式に変換
        $serializedBoard = [];
        foreach ($this->board as $y => $row) {
            $serializedBoard[$y] = $row;
            foreach ($row as $x => $tile) {
                $serializedBoard[$y][$x] = $tile->toArray();
            }
        }

        // 訪問済みタイルの位置を記録
        $visitedPositions = [];
        foreach ($this->visitedTiles as $tile) {
            $visitedPositions[] = ['x' => $tile->x(), 'y' => $tile->y()];
        }

        return [
            'board' => $serializedBoard,
            'width' => $this->width,
            'height' => $this->height,
            'numOfMines' => $this->numOfMines,
            'visitedTiles' => $visitedPositions,
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
            foreach ($data['board'] as $y => $row) {
                foreach ($row as $x => $tile) {
                    if (! $tile['isOpen']) {
                        $data['board'][$y][$x]['isMine'] = false;
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
        $requiredFields = ['width', 'height', 'numOfMines', 'board'];
        foreach ($requiredFields as $field) {
            if (! isset($serialized[$field])) {
                throw new InvalidArgumentException("不完全なゲームデータです: {$field}が欠けています");
            }
        }

        // ボードデータの型チェック
        if (! is_array($serialized['board'])) {
            throw new InvalidArgumentException('ボードデータが無効です');
        }

        // ボードサイズとwith, heightの一致チェック
        $isBoardWidthMatch = count($serialized['board'][0]) === $serialized['width'];
        $isBoardHeightMatch = count($serialized['board']) === $serialized['height'];
        if (! ($isBoardWidthMatch && $isBoardHeightMatch)) {
            throw new InvalidArgumentException('ボードサイズとwidth/heightが一致しません');
        }
        $width = $serialized['width'];
        $height = $serialized['height'];
        $numOfMines = $serialized['numOfMines'];

        $restoredBoard = GameService::createBoard($width, $height);

        // ボード状態を復元
        foreach ($serialized['board'] as $y => $row) {
            foreach ($row as $x => $tile) {
                $restoredBoard[$y][$x]->setOpen($tile['isOpen']);
                $restoredBoard[$y][$x]->setFlag($tile['isFlag']);
                $restoredBoard[$y][$x]->setMine($tile['isMine']);
                $restoredBoard[$y][$x]->setAdjacentMines($tile['adjacentMines']);
            }
        }

        $restoreState = new GameState($restoredBoard, $width, $height, $numOfMines);

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
}
