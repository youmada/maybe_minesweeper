<?php

namespace App\Domain\Minesweeper;

class MineCalculator
{
    public static function fromRatio(int $width, int $height, int $mineRatio): int
    {
        $totalTiles = $width * $height;

        return (int) ceil($totalTiles * $mineRatio / 100);
    }
}
