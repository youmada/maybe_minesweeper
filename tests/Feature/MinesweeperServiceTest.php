<?php

namespace Feature;

use App\Domain\Minesweeper\GameService;
use App\Domain\Minesweeper\GameState;
use App\Domain\Minesweeper\TileActionMode;
use App\Repositories\Composites\GameCompositeRepository;
use App\Repositories\DB\MinesweeperRepository as DBRepo;
use App\Repositories\Redis\MinesweeperRepository as RedisRepo;
use App\Services\Minesweeper\MinesweeperService;
use App\Utils\UUIDFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MinesweeperServiceTest extends TestCase
{
    use RefreshDatabase;

    protected MinesweeperService $mineSweeperService;

    protected int $width = 4;

    protected int $height = 4;

    protected int $numOfMines = 5;

    protected function setUp(): void
    {
        parent::setUp();
        $this->roomId = UUIDFactory::generate();
        $this->gameRepository = new GameCompositeRepository(new RedisRepo, new DBRepo);
        $this->mineSweeperService = new MinesweeperService($this->gameRepository);
    }

    #[Test]
    public function init_game(): void
    {
        // 実行
        $gameState = $this->mineSweeperService->initializeGame($this->roomId, $this->width, $this->height,
            $this->numOfMines);

        //検証

        $this->assertInstanceOf(GameState::class, $gameState, 'ゲーム状態が正しく初期化されていません');
        $this->assertEquals($this->width, $gameState->getWidth());
        $this->assertEquals($this->height, $gameState->getHeight());
    }

    #[Test]
    public function when_tile_click_by_open_action(): void
    {
        // 準備
        $gameState = $this->mineSweeperService->initializeGame($this->roomId, $this->width, $this->height,
            $this->numOfMines);
        $clickTileX = $this->width / 2;
        $clickTileY = $this->height / 2;

        // 実行
        // handleClickTileにはどのタイルをクリックしたのか、位置情報が必要
        $gameState = $this->mineSweeperService->handleClickTile($this->roomId, $clickTileX, $clickTileY,
            TileActionMode::OPEN);
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
        $gameState = $this->mineSweeperService->initializeGame($this->roomId, $this->width, $this->height,
            $this->numOfMines);
        $clickTileX = $this->width / 2;
        $clickTileY = $this->height / 2;

        // 実行
        // 1回目
        $firstClickGameState = $this->mineSweeperService
            ->handleClickTile($this->roomId, $clickTileX, $clickTileY, TileActionMode::OPEN);
        $firstBoard = $firstClickGameState->getBoard();
        $firstClickTile = GameService::getTile($firstBoard, $clickTileX, $clickTileY);

        // 2回目
        $secondClickGameState = $this->mineSweeperService
            ->handleClickTile($this->roomId, $clickTileX, $clickTileY, TileActionMode::OPEN);

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
        $gameState = $this->mineSweeperService->initializeGame($this->roomId, $this->width, $this->height,
            $this->numOfMines);
        $clickTileX = (int) ($this->width / 2);
        $clickTileY = (int) ($this->height / 2);

        $tiles = $gameState->getGameState();

        // 実行
        $gameState = $this->mineSweeperService->processGameStart($this->roomId, $clickTileX, $clickTileY);
        foreach ($tiles as $yIndex => $row) {
            foreach ($row as $xIndex => $tile) {
                if (! $tile->isMine()) {
                    $this->mineSweeperService->handleClickTile($this->roomId, $xIndex, $yIndex, TileActionMode::OPEN);
                }
            }
        }

        $gameState = $this->gameRepository->getState($this->roomId);

        // アサート：すべてのタイルが開放され、ゲームがクリアかを確認
        $this->assertTrue($gameState->isGameClear());

    }

    #[Test]
    public function if_the_game_is_over_when_a_tile_is_clicked(): void
    {
        // 準備
        $minimaMineRatio = 1;
        $gameState = $this->mineSweeperService->initializeGame($this->roomId, $this->width, $this->height,
            $minimaMineRatio);
        $clickTileX = $this->width / 2;
        $clickTileY = $this->height / 2;

        // クリック位置に手動で地雷をセット
        $gameState->getBoard()->getTile($clickTileX, $clickTileY)->setMine(true);
        $this->gameRepository->saveState($gameState, $this->roomId);

        $gameState = $this->mineSweeperService->handleClickTile($this->roomId, $clickTileX, $clickTileY,
            TileActionMode::OPEN);

        // アサート
        $this->assertTrue($gameState->isGameOver());
    }

    #[Test]
    public function when_a_tile_is_clicked_by_flag_mode(): void
    {
        // 準備
        $this->mineSweeperService->initializeGame($this->roomId, $this->width, $this->height, $this->numOfMines);
        $clickTileX = $this->width / 2;
        $clickTileY = $this->height / 2;

        // 実行
        $isFirstTileFlag = $this->mineSweeperService
            ->handleClickTile($this->roomId, $clickTileX, $clickTileY, TileActionMode::FLAG)
            ->getBoard()->getTile($clickTileX, $clickTileY)->isFlag();
        $isSecondTileFlag = $this->mineSweeperService
            ->handleClickTile($this->roomId, $clickTileX, $clickTileY, TileActionMode::FLAG)
            ->getBoard()->getTile($clickTileX, $clickTileY)->isFlag();

        // アサート
        $this->assertTrue($isFirstTileFlag);
        $this->assertFalse($isSecondTileFlag);

    }

    #[Test]
    public function when_game_started_mines_are_properly_placed(): void
    {
        // 準備
        $this->mineSweeperService->initializeGame($this->roomId, $this->width, $this->height, $this->numOfMines);
        $clickTileX = $this->width / 2;
        $clickTileY = $this->height / 2;

        // 実行
        $this->mineSweeperService->processGameStart($this->roomId, $clickTileX, $clickTileY);

        // 検証
        $gameState = $this->gameRepository->getState($this->roomId);
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
        $this->mineSweeperService->initializeGame($this->roomId, $this->width, $this->height, $this->numOfMines);
        $gameState = $this->gameRepository->getState($this->roomId);

        // 実行
        $clientData = $this->mineSweeperService->getGameStateForClient($gameState);

        // 検証
        $this->assertIsArray($clientData);
        // 必須キーの存在のみ確認
        $this->assertArrayHasKey('tileStates', $clientData);
        $this->assertArrayHasKey('width', $clientData);
        $this->assertArrayHasKey('height', $clientData);
        // その他の検証はGameState::toClientArrayのテストに任せる
    }
}
