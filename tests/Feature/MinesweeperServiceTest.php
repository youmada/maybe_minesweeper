<?php

namespace Feature;

use App\Domain\Minesweeper\GameService;
use App\Domain\Minesweeper\GameState;
use App\Domain\Minesweeper\TileActionMode;
use App\Services\Minesweeper\MinesweeperService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MinesweeperServiceTest extends TestCase
{
    use RefreshDatabase;

    protected MinesweeperService $mineSweeperService;

    protected int $width = 10;

    protected int $height = 10;

    protected int $mineRatio = 30;

    protected function setUp(): void
    {
        parent::setUp();

        $gameService = new GameService;
        $this->mineSweeperService = new MinesweeperService($gameService);
    }

    #[Test]
    public function init_game(): void
    {
        // 実行
        $this->mineSweeperService->initializeGame($this->width, $this->height, $this->mineRatio);

        //検証
        // getGameStateメソッドでゲーム状態を取得
        $gameState = $this->mineSweeperService->getGameState();

        $this->assertInstanceOf(GameState::class, $gameState, 'ゲーム状態が正しく初期化されていません');
        $this->assertEquals($this->width, $gameState->getWidth());
        $this->assertEquals($this->height, $gameState->getHeight());
    }

    #[Test]
    public function when_tile_click_by_open_action(): void
    {
        // 準備
        $this->mineSweeperService->initializeGame($this->width, $this->height, $this->mineRatio);
        $clickTileX = $this->width / 2;
        $clickTileY = $this->height / 2;

        // 実行
        // handleClickTileにはどのタイルをクリックしたのか、位置情報が必要
        $gameState = $this->mineSweeperService->handleClickTile($clickTileX, $clickTileY, TileActionMode::OPEN);
        $currentBoard = $gameState->getBoard();
        $currentClickTile = GameService::getTile($currentBoard, $clickTileX, $clickTileY);

        // アサート
        $this->assertInstanceOf(GameState::class, $gameState);
        $this->assertTrue($gameState->isTileVisited($currentClickTile));
    }

    #[Test]
    public function when_tile_click_by_open_action_again(): void
    {
        // 準備
        $this->mineSweeperService->initializeGame($this->width, $this->height, $this->mineRatio);
        $clickTileX = $this->width / 2;
        $clickTileY = $this->height / 2;

        // 実行
        // 1回目
        $firstClickGameState = $this->mineSweeperService
            ->handleClickTile($clickTileX, $clickTileY, TileActionMode::OPEN);
        $firstBoard = $firstClickGameState->getBoard();
        $firstClickTile = GameService::getTile($firstBoard, $clickTileX, $clickTileY);

        // 2回目
        $secondClickGameState = $this->mineSweeperService
            ->handleClickTile($clickTileX, $clickTileY, TileActionMode::OPEN);

        $secondBoard = $secondClickGameState->getBoard();
        $secondClickTile = GameService::getTile($secondBoard, $clickTileX, $clickTileY);

        // アサート
        // 想定しているのは、同じ部分をクリックしても何も変わらないこと
        $this->assertEquals($firstClickTile, $secondClickTile);
    }

    #[Test]
    public function if_the_game_is_cleared_when_a_tile_is_clicked(): void
    {
        // 準備
        $minimaMineRatio = 1;
        $this->initializeTestGameWithMinimalMines($this->width, $this->height, $minimaMineRatio);
        $clickTileX = (int) ($this->width / 2);
        $clickTileY = (int) ($this->height / 2);

        // 実行 地雷は無しなので、瞬時にゲームクリアする
        $this->mineSweeperService->processGameStart($clickTileX, $clickTileY);

        // アサート：すべてのタイルが開放され、ゲームがクリアかを確認
        $gameState = $this->mineSweeperService->getGameState();
        $this->assertTrue($gameState->isGameClear());

    }

    #[Test]
    public function if_the_game_is_over_when_a_tile_is_clicked(): void
    {
        // 準備
        $this->mineSweeperService->initializeGame($this->width, $this->height, $this->mineRatio);
        $clickTileX = $this->width / 2;
        $clickTileY = $this->height / 2;

        // クリック位置に手動で地雷をセット
        $this->mineSweeperService->getGameState()->getBoard()->getTile($clickTileX, $clickTileY)->setMine(true);

        $gameState = $this->mineSweeperService->handleClickTile($clickTileX, $clickTileY, TileActionMode::OPEN);

        // アサート
        $this->assertTrue($this->mineSweeperService->getGameState()->isGameOver());
    }

    #[Test]
    public function when_a_tile_is_clicked_by_flag_mode(): void
    {
        // 準備
        $this->mineSweeperService->initializeGame($this->width, $this->height, $this->mineRatio);
        $clickTileX = $this->width / 2;
        $clickTileY = $this->height / 2;

        // 実行
        $isFirstTileFlag = $this->mineSweeperService
            ->handleClickTile($clickTileX, $clickTileY, TileActionMode::FLAG)
            ->getBoard()->getTile($clickTileX, $clickTileY)->isFlag();
        $isSecondTileFlag = $this->mineSweeperService
            ->handleClickTile($clickTileX, $clickTileY, TileActionMode::FLAG)
            ->getBoard()->getTile($clickTileX, $clickTileY)->isFlag();

        // アサート
        $this->assertTrue($isFirstTileFlag);
        $this->assertFalse($isSecondTileFlag);

    }

    #[Test]
    public function when_game_started_mines_are_properly_placed(): void
    {
        // 準備
        $this->mineSweeperService->initializeGame($this->width, $this->height, $this->mineRatio);
        $clickTileX = $this->width / 2;
        $clickTileY = $this->height / 2;

        // 実行
        $this->mineSweeperService->processGameStart($clickTileX, $clickTileY);

        // 検証
        $gameState = $this->mineSweeperService->getGameState();
        $board = $gameState->getBoard();

        // 1. ゲームが開始状態になっているか
        $this->assertTrue($gameState->isGameStarted());

        // 2. 地雷が適切な数配置されているか
        $mineCount = $board->countMines();
        $this->assertEquals($gameState->getNumOfMines(), $mineCount);

        // 3. クリックしたタイルとその周辺に地雷がないか
        $this->assertFalse($board->getTile($clickTileX, $clickTileY)->isMine());
        foreach (GameService::getAroundTiles($board, $clickTileX, $clickTileY) as $tile) {
            $this->assertFalse($tile->isMine());
        }

        // 4. クリックしたタイルが開かれているか
        $this->assertTrue($board->getTile($clickTileX, $clickTileY)->isOpen());
    }

    #[Test]
    public function getGameStateForClient_returns_data_in_expected_format(): void
    {
        // 準備
        $this->mineSweeperService->initializeGame($this->width, $this->height, $this->mineRatio);

        // 実行
        $clientData = $this->mineSweeperService->getGameStateForClient();

        // 検証
        $this->assertIsArray($clientData);
        // 必須キーの存在のみ確認
        $this->assertArrayHasKey('board', $clientData);
        $this->assertArrayHasKey('width', $clientData);
        $this->assertArrayHasKey('height', $clientData);
        // その他の検証はGameState::toClientArrayのテストに任せる
    }

    private function initializeTestGameWithMinimalMines(int $width, int $height, int $mineRatio): GameState
    {
        $game = $this->mineSweeperService->initializeGame($width, $height, $mineRatio);

        // 必ず地雷がない状況を作成
        foreach ($game->getGameState()->getBoard()->getBoard() as $row) {
            foreach ($row as $tile) {
                $tile->setMine(false);
            }
        }

        return $game->getGameState();
    }
}
