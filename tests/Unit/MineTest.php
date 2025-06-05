<?php

namespace Unit;

use App\Domain\Minesweeper\GameService;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class MineTest extends TestCase
{
    #[Test]
    public function set_Mines()
    {
        $board = GameService::createBoard(3, 3);
        $boardTiles = $board->getBoardState();
        $firstClick = ['x' => 0, 'y' => 0];
        $numOfMines = 1;

        GameService::setMines($board, $numOfMines, $firstClick);

        $mineCount = 0;
        foreach ($boardTiles as $row) {
            foreach ($row as $tile) {
                if ($tile->isMine()) {
                    $mineCount++;
                }
            }
        }

        $this->assertEquals($numOfMines, $mineCount);
    }

    #[Test]
    public function no_mines_around_first_click()
    {
        $board = GameService::createBoard(10, 10);
        $boardTiles = $board->getBoardState();
        $firstClick = ['x' => 5, 'y' => 5];
        $numOfMines = 20;

        GameService::setMines($board, $numOfMines, $firstClick);

        $aroundTiles = [
            ['x' => 4, 'y' => 4],
            ['x' => 4, 'y' => 5],
            ['x' => 4, 'y' => 6],
            ['x' => 5, 'y' => 4],
            ['x' => 5, 'y' => 6],
            ['x' => 6, 'y' => 4],
            ['x' => 6, 'y' => 5],
            ['x' => 6, 'y' => 6],
        ];

        foreach ($aroundTiles as $coords) {
            $this->assertFalse($boardTiles[$coords['y']][$coords['x']]->isMine());
        }
    }

    #[Test]
    public function too_many_mines()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('地雷数が多すぎます');

        $board = GameService::createBoard(3, 3);
        $firstClick = ['x' => 0, 'y' => 0];
        $numOfMines = 10;

        GameService::setMines($board, $numOfMines, $firstClick);
    }

    #[Test]
    public function invalid_first_click()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('初クリック位置が不正です');

        $board = GameService::createBoard(3, 3);
        $firstClick = ['x' => 10, 'y' => 10];
        $numOfMines = 1;

        GameService::setMines($board, $numOfMines, $firstClick);
    }
}
