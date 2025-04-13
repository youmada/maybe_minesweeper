<?php

namespace Unit;

use App\Domain\Minesweeper\GameService;
use App\Domain\Minesweeper\GameState;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class BoardTest extends TestCase
{
    /**
     * ボードの初期化テスト
     */
    #[Test]
    public function board_initialization()
    {
        $board = GameService::createBoard(10, 5)->getBoard();

        $this->assertCount(5, $board); // height
        $this->assertCount(10, $board[0]); // width

        $index = 0;
        foreach ($board as $row) {
            foreach ($row as $ignored) {
                $index++;
            }
        }
        $this->assertEquals(50, $index);
    }

    // ボードの初期状態テスト
    #[Test]
    public function board_initialization_state()
    {
        $board = GameService::createBoard(10, 5)->getBoard();

        foreach ($board as $yIndex => $row) {
            foreach ($row as $xIndex => $tile) {
                $this->assertEquals([
                    'x' => $xIndex,
                    'y' => $yIndex,
                    'isMine' => false,
                    'isOpen' => false,
                    'isFlag' => false,
                    'adjacentMines' => 0,
                ], $tile->toArray());
            }
        }
    }

    #[Test]
    public function board_size_as_zero()
    {
        $board = GameService::createBoard(0, 0);
        $this->assertNotInstanceOf(GameState::class, $board);
    }
}
