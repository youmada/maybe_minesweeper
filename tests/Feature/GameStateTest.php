<?php

namespace Tests\Feature;

use App\Domain\Minesweeper\GameService;
use App\Domain\Minesweeper\GameState;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GameStateTest extends TestCase
{
    #[Test]
    public function to_array_returns_complete_game_state(): void
    {
        $width = 10;
        $height = 10;
        $mineRatio = 10;
        $board = GameService::createBoard($width, $height);
        $gameState = new GameState($board, $width, $height, $mineRatio);

        // 実行
        $result = $gameState->toArray();

        // 検証
        $this->assertIsArray($result);

        // 必要なキーが全て含まれているか確認
        $this->assertArrayHasKey('board', $result);
        $this->assertArrayHasKey('width', $result);
        $this->assertArrayHasKey('height', $result);
        $this->assertArrayHasKey('numOfMines', $result);
        $this->assertArrayHasKey('visitedTiles', $result);
        $this->assertArrayHasKey('isGameStarted', $result);
        $this->assertArrayHasKey('isGameOver', $result);
        $this->assertArrayHasKey('isGameClear', $result);

        // 値の検証
        $this->assertEquals($width, $result['width']);
        $this->assertEquals($height, $result['height']);
        $this->assertEquals($mineRatio, $result['numOfMines']);
        $this->assertFalse($result['isGameStarted']);
        $this->assertFalse($result['isGameOver']);
        $this->assertFalse($result['isGameClear']);

        // ボード構造の検証
        $this->assertCount($height, $result['board']);
        $this->assertCount($width, $result['board'][0]);
    }

    #[Test]
    public function to_client_array_complete_game_state(): void
    {
        // 準備
        $width = 5;
        $height = 5;
        $numOfMines = 5;
        $board = GameService::createBoard($width, $height);

        // 特定の位置に地雷を配置（例: [0,0], [1,1], [2,2], [3,3], [4,4]）
        $board[0][0]->setMine(true);
        $board[1][1]->setMine(true);
        $board[2][2]->setMine(true);
        $board[3][3]->setMine(true);
        $board[4][4]->setMine(true);

        // いくつかのタイルを開く（例: [0,1], [1,0]）
        $board[0][1]->setOpen(true);
        $board[1][0]->setOpen(true);

        $gameState = new GameState($board, $width, $height, $numOfMines);
        $gameState->startGame(); // ゲームを開始状態にする

        // 実行
        $clientArray = $gameState->toClientArray();

        // 検証
        // 開いてないタイルの地雷情報が隠されているか
        $this->assertFalse($clientArray['board'][0][0]['isMine'], '開いてないタイルの地雷情報が隠されていません');
        $this->assertFalse($clientArray['board'][1][1]['isMine'], '開いてないタイルの地雷情報が隠されていません');

        // 開いているタイルは影響なし（この例では地雷なしのタイル）
        $this->assertFalse($clientArray['board'][0][1]['isMine']);
        $this->assertFalse($clientArray['board'][1][0]['isMine']);
    }

    #[Test]
    public function to_client_array_shows_mines_when_game_is_over(): void
    {
        // 準備
        $width = 5;
        $height = 5;
        $numOfMines = 5;
        $board = GameService::createBoard($width, $height);

        // 特定の位置に地雷を配置（例: [0,0], [1,1], [2,2], [3,3], [4,4]）
        $board[0][0]->setMine(true);
        $board[1][1]->setMine(true);
        $board[2][2]->setMine(true);
        $board[3][3]->setMine(true);
        $board[4][4]->setMine(true);

        // いくつかのタイルを開く（例: [0,1], [1,0]）
        $board[0][1]->setOpen(true);
        $board[1][0]->setOpen(true);
        // 地雷のあるタイルも開く（ゲームオーバーの状況）
        $board[0][0]->setOpen(true);

        $gameState = new GameState($board, $width, $height, $numOfMines);
        $gameState->startGame();
        $gameState->endGame(false); // ゲームオーバー状態にする

        // 実行
        $clientArray = $gameState->toClientArray();

        // 検証 - 全ての地雷情報が表示されるか
        $this->assertTrue($clientArray['board'][0][0]['isMine'], 'ゲームオーバー時に地雷情報が表示されていません');
        $this->assertTrue($clientArray['board'][1][1]['isMine'], 'ゲームオーバー時に地雷情報が表示されていません');
        $this->assertTrue($clientArray['board'][2][2]['isMine'], 'ゲームオーバー時に地雷情報が表示されていません');
        $this->assertTrue($clientArray['board'][3][3]['isMine'], 'ゲームオーバー時に地雷情報が表示されていません');
        $this->assertTrue($clientArray['board'][4][4]['isMine'], 'ゲームオーバー時に地雷情報が表示されていません');
    }

    #[Test]
    public function game_state_survives_round_trip_serialization()
    {
        // 準備 - 特定の状態を持つゲーム状態を作成
        $width = 5;
        $height = 5;
        $numOfMines = 5;
        $board = GameService::createBoard($width, $height);
        // 地雷の配置
        $board[0][0]->setMine(true);
        $board[1][1]->setMine(true);

        // タイルを開く
        $board[2][2]->setOpen(true);

        $originalGameState = new GameState($board, $width, $height, $numOfMines);
        $originalGameState->startGame();

        $visitedTiles = $originalGameState->getVisitedTiles();
        $visitedTiles->attach($board[2][2]); // 開いたタイルを追加

        // 実行 - シリアライズとデシリアライズ
        $serialized = $originalGameState->toArray();
        //        dump($serialized);
        $restoredState = GameState::fromArray($serialized);

        // 検証 - 元の状態と復元後の状態を比較
        $this->assertEquals($width, $restoredState->getWidth());
        $this->assertEquals($height, $restoredState->getHeight());
        $this->assertEquals($numOfMines, $restoredState->getNumOfMines());
        $this->assertTrue($restoredState->isGameStarted());
        $this->assertFalse($restoredState->isGameOver());
        $this->assertFalse($restoredState->isGameClear());

        // ボードの状態検証
        $restoredBoard = $restoredState->getBoard();
        $this->assertTrue($restoredBoard[0][0]->isMine());
        $this->assertTrue($restoredBoard[1][1]->isMine());
        $this->assertTrue($restoredBoard[2][2]->isOpen());

        // 訪問済みタイルの検証
        $restoredVisitedTiles = $restoredState->getVisitedTiles();
        $this->assertTrue($restoredVisitedTiles->contains($restoredBoard[2][2]));
        $this->assertEquals(1, $restoredVisitedTiles->count());
    }

    #[Test]
    public function from_array_throws_exception_with_incomplete_data()
    {
        // 期待される例外を設定
        $this->expectException(InvalidArgumentException::class);

        // 不完全なデータを準備
        $incompleteData = [
            // 必須フィールドが欠けている例
            'width' => 5,
            'height' => 5,
            // numOfMinesがない
            // boardがない
        ];

        // 例外がスローされることを期待しているメソッドを実行
        GameState::fromArray($incompleteData);

        // ここには到達しないはず（例外が発生すれば）
    }

    #[Test]
    public function from_array_throws_exception_with_invalid_board_data(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('ボードデータが無効です');

        $invalidData = [
            'width' => 5,
            'height' => 5,
            'numOfMines' => 5,
            'board' => 'これは配列ではなく文字列',  // 無効なボードデータ
            'visitedPositions' => [],
            'isGameStarted' => false,
            'isGameOver' => false,
            'isGameClear' => false,
        ];

        GameState::fromArray($invalidData);
    }

    #[Test]
    public function from_array_throws_exception_with_size_mismatch(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('ボードサイズとwidth/heightが一致しません');

        // ボードサイズとwidth/heightが一致しないデータ
        $mismatchData = [
            'width' => 5,
            'height' => 5,
            'numOfMines' => 5,
            'board' => array_fill(0, 3, array_fill(0, 3, [])),  // 3x3 ボード
            'visitedPositions' => [],
            'isGameStarted' => false,
            'isGameOver' => false,
            'isGameClear' => false,
        ];

        GameState::fromArray($mismatchData);
    }
}
