<?php

namespace Tests\Unit;

use App\Domain\Minesweeper\GameService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class BoardTest extends TestCase
{

    /**
     * ボードの初期化テスト
     */
    #[Test] public function board_nitialization() {
        $board = GameService::createBoard(10, 5);

        $this->assertEquals(5, count($board)); // height
        $this->assertEquals(10, count($board[0])); // width

        $index = 0;
        foreach ($board as $row) {
            foreach ($row as $tile) {
                $index++;
            }
        }
        $this->assertEquals(50, $index);
    }


    // ボードの初期状態テスト
    #[Test] public function board_initialization_state() {
        $board = GameService::createBoard(10, 5);

        foreach ($board as $yIndex => $row) {
            foreach ($row as $xIndex => $tile) {
                $this->assertEquals([
                    'x' => $xIndex,
                    'y' => $yIndex,
                    'isMine' => false,
                    'isOpen' => false,
                    'isFlag' => false,
                ], $tile->toArray());
            }
        }
    }

    #[Test] public function board_size_as_zero() {
        $board = GameService::createBoard(0, 0);
        $this->assertEquals(0, count($board));
    }


}
