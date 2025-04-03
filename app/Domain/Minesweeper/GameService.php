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


    /**
     * 周囲のタイルを取得する
     *
     * @param array<array<Tile>> $board
     * @param int $x
     * @param int $y
     * @return array<Tile>
     */
    public static function getAroundTiles(array $board, int $x, int $y):array
    {
        if (empty($board)) {
            return [];
        }


        $height = count($board);
        $width = count($board[0]);
        $aroundTiles = [];

        // 相対位置を指定して時計回りに周囲のタイルを取得
        $directions = [
            [0, -1], [1, -1], [1, 0], [1, 1], [0, 1], [-1, 1], [-1, 0], [-1, -1]
        ];

        foreach ($directions as [$dx, $dy]) {
            $nx = $x + $dx;
            $ny = $y + $dy;

            if ($nx >= 0 && $nx < $width && $ny >= 0 && $ny < $height) {
                $aroundTiles[] = $board[$ny][$nx];
            }
        }

        return $aroundTiles;
    }


}
