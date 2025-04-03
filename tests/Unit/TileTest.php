<?php

namespace Tests\Unit;

use App\Domain\Minesweeper\GameService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class TileTest extends TestCase
{

    #[Test] public function get_around_tiles() {
        $board = GameService::createBoard(10, 10);

        $aroundTiles = GameService::getAroundTiles($board, 5,5);


        $expected = [
            ['x' => 5, 'y' => 4],
            ['x' => 6, 'y' => 4],
            ['x' => 6, 'y' => 5],
            ['x' => 6, 'y' => 6],
            ['x' => 5, 'y' => 6],
            ['x' => 4, 'y' => 6],
            ['x' => 4, 'y' => 5],
            ['x' => 4, 'y' => 4],
        ];
        foreach ($aroundTiles as $index => $tile) {
            $this->assertEquals($expected[$index]['x'], $tile->x());
            $this->assertEquals($expected[$index]['y'], $tile->y());
        }
    }
}
