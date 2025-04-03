<?php

namespace App\Domain\Minesweeper;

class GameService
{
    /**
     * ボードを作成する
     *
     * @param int $width
     * @param int $height
     * @return array<array<Tile>>
     */
    public static function createBoard(int $width, int $height):array
    {
        if ($width <= 0 || $height <= 0) {
            return [];
        }
        $board = [];

        for($y = 0; $y < $height; $y++) {
            $board[$y] = [];
            for($x = 0; $x < $width; $x++) {
                $board[$y][$x] = new Tile($x,$y);
            }
        }
        return $board;
    }


}
