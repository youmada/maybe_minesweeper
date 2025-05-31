<?php

namespace App\Domain\Minesweeper;

class Board
{
    private int $width;

    private int $height;

    /**
     * @var Tile[][] 二次元配列でTileオブジェクトを格納
     */
    private array $tiles;

    public function __construct(int $width, int $height)
    {
        $this->width = $width;
        $this->height = $height;
        $this->initializeBoard();
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    private function initializeBoard(): void
    {
        if ($this->width <= 0 || $this->height <= 0) {
            return;
        }
        $board = [];

        for ($y = 0; $y < $this->height; $y++) {
            $board[$y] = [];
            for ($x = 0; $x < $this->width; $x++) {
                $board[$y][$x] = new Tile($x, $y);
            }
        }
        $this->tiles = $board;
    }

    public function getBoard(): array
    {
        return $this->tiles;
    }

    public function getTile(int $x, int $y): ?Tile
    {
        if ($x < 0 || $x >= $this->width || $y < 0 || $y >= $this->height) {
            return null;
        }

        return $this->tiles[$y][$x];
    }

    public function countMines(): int
    {
        $count = 0;
        for ($y = 0; $y < $this->height; $y++) {
            for ($x = 0; $x < $this->width; $x++) {
                if ($this->tiles[$y][$x]->isMine()) {
                    $count++;
                }
            }
        }

        return $count;
    }
}
