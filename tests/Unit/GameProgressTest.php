<?php

namespace Tests\Unit;

use App\Domain\Minesweeper\GameService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class GameProgressTest extends TestCase
{
   #[Test] public function check_game_over()
    {
        $board = GameService::createBoard(3, 3);
        $clickedTile = $board->getTile(0,0);

        $clickedTile->setMine(true);
        $clickedTile->setOpen(true);

        $result = GameService::checkGameOver($clickedTile);

        $this->assertTrue($result);
    }

    #[Test] public function check_game_over_false()
    {
        $board = GameService::createBoard(3, 3);
        $clickedTile = $board->getTile(0,0);

        $clickedTile->setOpen(true);

        $result = GameService::checkGameOver($clickedTile);

        $this->assertFalse($result);
    }

    #[Test]  public function check_game_clear()
    {
        $board = GameService::createBoard(3, 3);
        $totalMines = 1;

        $firstClick = ['x' => 0, 'y' => 0];
        GameService::setMines($board, $totalMines, $firstClick);

        foreach ($board->getBoard() as $row) {
            foreach ($row as $tile) {
                if (!$tile->isMine()) {
                    $tile->setOpen(true);
                }
            }
        }


        $result = GameService::checkGameClear($board, $totalMines);

        $this->assertTrue($result);
    }

    #[Test] public function check_game_clear_false()
    {
        $board = GameService::createBoard(3, 3);
        $totalMines = 1;

        $firstClick = ['x' => 0, 'y' => 0];
        GameService::setMines($board, $totalMines, $firstClick);

        foreach ($board as $row) {
            foreach ($row as $tile) {
                if (!$tile->isMine()) {
                    $tile->setOpen(true);
                }
            }
        }

        // 1つだけ閉じる
        $board->getTile(0,1)->setOpen(false);

        $result = GameService::checkGameClear($board, $totalMines);

        $this->assertFalse($result);
    }
}
