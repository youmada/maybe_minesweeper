<?php

namespace App\Domain\Minesweeper;

class GameService
{
    /**
     * ボードを作成する
     */
    public static function createBoard(int $width, int $height): Board
    {
        return new Board($width, $height);
    }

    /**
     * 周囲のタイルを取得する
     *
     * @return array<Tile>
     */
    public static function getAroundTiles(Board $board, int $x, int $y): array
    {
        $boardTiles = $board->getBoard();
        if (empty($boardTiles)) {
            return [];
        }

        $height = count($boardTiles);
        $width = count($boardTiles[0]);
        $aroundTiles = [];

        // 相対位置を指定して時計回りに周囲のタイルを取得
        $directions = [
            [0, -1], [1, -1], [1, 0], [1, 1], [0, 1], [-1, 1], [-1, 0], [-1, -1],
        ];

        foreach ($directions as [$dx, $dy]) {
            $nx = $x + $dx;
            $ny = $y + $dy;

            if ($nx >= 0 && $nx < $width && $ny >= 0 && $ny < $height) {
                $aroundTiles[] = $boardTiles[$ny][$nx];
            }
        }

        return $aroundTiles;
    }

    // xとy座標のタイルを取得する
    public static function getTile(Board $board, int $x, int $y): ?Tile
    {
        return $board->getTile($x, $y);
    }

    /**
     * 地雷を設置する
     *
     * @param  int  $numOfMines  // パーセンテージ（0 - 100)
     * @param  array{x: int, y: int}  $firstClick
     *
     * @throws \InvalidArgumentException
     */
    public static function setMines(Board $board, int $numOfMines, array $firstClick): void
    {
        $boardTiles = $board->getBoard();
        if (empty($boardTiles)) {
            return;
        }

        $height = count($boardTiles);
        $width = count($boardTiles[0]);

        // 初クリック位置チェック
        if ($firstClick['x'] < 0 || $firstClick['x'] >= $width ||
            $firstClick['y'] < 0 || $firstClick['y'] >= $height) {
            throw new \InvalidArgumentException('初クリック位置が不正です');
        }

        // 初クリック位置とその周囲を除外する位置に設定
        $excludePositions = [];
        for ($dy = -1; $dy <= 1; $dy++) {
            for ($dx = -1; $dx <= 1; $dx++) {
                $nx = $firstClick['x'] + $dx;
                $ny = $firstClick['y'] + $dy;
                if ($nx >= 0 && $nx < $width && $ny >= 0 && $ny < $height) {
                    $excludePositions[] = ['x' => $nx, 'y' => $ny];
                }
            }
        }

        // 地雷を設置可能な位置数
        $availableTiles = $width * $height - count($excludePositions);

        if ($numOfMines > $availableTiles) {
            throw new \InvalidArgumentException('地雷数が多すぎます');
        }

        // 地雷設置
        $minesToPlace = $numOfMines;
        while ($minesToPlace > 0) {
            $x = mt_rand(0, $width - 1);
            $y = mt_rand(0, $height - 1);

            // 除外位置をチェック
            $exclude = false;
            foreach ($excludePositions as $pos) {
                if ($x === $pos['x'] && $y === $pos['y']) {
                    $exclude = true;
                    break;
                }
            }

            if (! $exclude && ! $boardTiles[$y][$x]->isMine()) {
                $boardTiles[$y][$x]->setMine(true);
                $minesToPlace--;

                // 周辺タイルの地雷カウントを更新
                for ($dy = -1; $dy <= 1; $dy++) {
                    for ($dx = -1; $dx <= 1; $dx++) {
                        if ($dx === 0 && $dy === 0) {
                            continue;
                        }

                        $nx = $x + $dx;
                        $ny = $y + $dy;
                        if ($nx >= 0 && $nx < $width && $ny >= 0 && $ny < $height) {
                            $boardTiles[$ny][$nx]->incrementAdjacentMines();
                        }
                    }
                }
            }
        }
    }

    /**
     * タイルを開く
     */
    public static function openTile(Board $board, Tile $tile, \SplObjectStorage $visitedTiles): void
    {
        // すでに開かれているタイルは処理しない
        if ($tile->isOpen()) {
            return;
        }

        // すでに処理済みのタイルは処理しない
        if ($visitedTiles->contains($tile)) {
            return;
        }

        // 地雷なら終了
        if ($tile->isMine()) {
            return;
        }

        // 訪問済みに追加
        $visitedTiles->attach($tile);

        // フラグが立っている場合は外す
        if ($tile->isFlag()) {
            $tile->setFlag(false);
        }

        // タイルを開く
        $tile->setOpen(true);

        // 周囲に地雷がなければ、周囲のタイルも再帰的に開く
        if ($tile->adjacentMines() === 0) {
            $aroundTiles = self::getAroundTiles($board, $tile->x(), $tile->y());
            foreach ($aroundTiles as $aroundTile) {
                self::openTile($board, $aroundTile, $visitedTiles);
            }
        }
    }

    /**
     * フラグを切り替える
     */
    public static function toggleFlag(Tile $tile): void
    {
        // 開いているタイルにはフラグを立てられない
        if ($tile->isOpen()) {
            return;
        }

        $tile->setFlag(! $tile->isFlag());
    }

    /**
     * ゲームオーバーをチェック
     */
    public static function checkGameOver(Tile $tile): bool
    {
        return $tile->isMine();
    }

    /**
     * ゲームクリアをチェック
     */
    public static function checkGameClear(Board $board, int $totalMines): bool
    {
        $closedTiles = 0;

        $boardTiles = $board->getBoard();

        $totalTiles = $board->getWidth() * $board->getHeight();

        foreach ($boardTiles as $row) {
            foreach ($row as $tile) {
                if (! $tile->isOpen()) {
                    $closedTiles++;
                }
            }
        }

        $restTiles = $totalMines - $closedTiles;

        return $restTiles === $totalMines;
    }
}
