<?php

namespace App\Domain\Minesweeper;

class Tile
{
    private int $x;
    private int $y;
    private bool $isMine;
    private bool $isOpen;
    private bool $isFlag;
//    private int $adjacentMines;
    /**
     * @param  int  $x
     * @param  int  $y
     */
    public function __construct(int $x, int $y)
    {
        $this->x = $x;
        $this->y = $y;
        $this->isMine = false;
        $this->isOpen = false;
        $this->isFlag = false;
//        $this->adjacentMines = 0;
    }

    public function x(): int
    {
        return $this->x;
    }

    public function y(): int
    {
        return $this->y;
    }

    public function isMine(): bool
    {
        return $this->isMine;
    }

    public function isOpen(): bool
    {
        return $this->isOpen;
    }

    public function isFlag(): bool
    {
        return $this->isFlag;
    }

//    public function adjacentMines(): int
//    {
//        return $this->adjacentMines;
//    }

    public function setMine(bool $value): void
    {
        $this->isMine = $value;
    }

    public function setOpen(bool $value): void
    {
        $this->isOpen = $value;
    }

    public function setFlag(bool $value): void
    {
        $this->isFlag = $value;
    }

//    public function incrementAdjacentMines(): void
//    {
//        $this->adjacentMines++;
//    }

    public function toArray(): array
    {
        return [
            'x' => $this->x,
            'y' => $this->y,
            'isMine' => $this->isMine,
            'isOpen' => $this->isOpen,
            'isFlag' => $this->isFlag,
//            'adjacentMines' => $this->adjacentMines
        ];
    }
}
