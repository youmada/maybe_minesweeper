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

    #[Test] public function get_around_tiles_at_edge()
    {
        $board = GameService::createBoard(10, 10);

        $aroundTiles = GameService::getAroundTiles($board, 0, 0);

        $expected = [
            ['x' => 1, 'y' => 0],
            ['x' => 1, 'y' => 1],
            ['x' => 0, 'y' => 1],
        ];

        $this->assertCount(3, $aroundTiles);
        foreach ($aroundTiles as $index => $tile) {
            $this->assertEquals($expected[$index]['x'], $tile->x());
            $this->assertEquals($expected[$index]['y'], $tile->y());
        }
    }


   #[Test] public function open_tile()
    {
        $board = GameService::createBoard(10, 10);
        $tile = $board[5][5];
        $visitedTiles = new \SplObjectStorage();

        GameService::openTile($board, $tile, $visitedTiles);

        $this->assertTrue($board[5][5]->isOpen());
    }

    #[Test] public function open_already_opened_tile()
    {
        $board = GameService::createBoard(5, 5);
        $tile = $board[2][2];
        $tile->setOpen(true);
        // フラグを立てておく(処理が早期リターンしていることを確認するため)
        $tile->setFlag(true);

        $visitedTiles = new \SplObjectStorage();
        $visitedTiles->attach($tile);

        GameService::openTile($board, $tile, $visitedTiles);

        $this->assertTrue($tile->isFlag());
    }


    #[Test] public function open_tile_with_cascade()
    {
        $board = GameService::createBoard(10, 10);
        $board[3][3]->setMine(true);
        $tile = $board[5][5];
        $visitedTiles = new \SplObjectStorage();

        GameService::openTile($board, $tile, $visitedTiles);

        $aroundCoords = [
            [4, 4], [4, 5], [4, 6],
            [5, 4], [5, 6],
            [6, 4], [6, 5], [6, 6]
        ];

        foreach ($aroundCoords as $cord) {
            $this->assertTrue($board[$cord[0]][$cord[1]]->isOpen());
        }
    }

   #[Test] public function open_flagged_tile()
    {
        $board = GameService::createBoard(10, 10);
        $tile = $board[5][5];
        $tile->setFlag(true);
        $visitedTiles = new \SplObjectStorage();

        GameService::openTile($board, $tile, $visitedTiles);

        $this->assertFalse($tile->isFlag());
    }

   #[Test] public function toggle_flag()
    {
        $board = GameService::createBoard(10, 10);
        $tile = $board[5][5];

        GameService::toggleFlag($tile);
        $this->assertTrue($tile->isFlag());

        GameService::toggleFlag($tile);
        $this->assertFalse($tile->isFlag());
    }

    public function testToggleFlagOnOpenTile()
    {
        $board = GameService::createBoard(10, 10);
        $tile = $board[5][5];
        $tile->setOpen(true);

        GameService::toggleFlag($tile);
        $this->assertFalse($tile->isFlag());
    }
}
