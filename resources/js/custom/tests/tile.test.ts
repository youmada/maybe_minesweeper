import { describe, expect, it } from 'vitest';
import {
    createBoard,
    getAroundTiles,
    openTile,
    toggleFlag,
} from '../domain/mineSweeper';

describe('タイル操作テスト', () => {
    // getAroundTilesの想定動作

    // 1. クリック周囲のタイルを取得できる
    // 2. クリック位置がボードの端の場合、端のタイルのみ取得できる
    describe('getAroundTilesテスト', () => {
        it('クリック周囲のタイルを取得できる', () => {
            const board = createBoard(10, 10);

            const aroundTiles = getAroundTiles(board, 5, 5);

            // 時計回りに取得されることを想定
            const expected = [
                { x: 5, y: 4 },
                { x: 6, y: 4 },
                { x: 6, y: 5 },
                { x: 6, y: 6 },
                { x: 5, y: 6 },
                { x: 4, y: 6 },
                { x: 4, y: 5 },
                { x: 4, y: 4 },
            ];
            aroundTiles.forEach((tile, i) => {
                expect({ x: tile.x, y: tile.y }).toEqual({
                    x: expected[i].x,
                    y: expected[i].y,
                });
            });
        });

        it('クリック位置がボードの端の場合、端のタイルのみ取得できる', () => {
            const board = createBoard(10, 10);

            const aroundTiles = getAroundTiles(board, 0, 0);

            // 時計回りに取得されることを想定
            const expected = [
                { x: 1, y: 0 }, // 右
                { x: 1, y: 1 }, // 右下
                { x: 0, y: 1 }, // 下
            ];
            aroundTiles.forEach((tile, i) => {
                expect({ x: tile.x, y: tile.y }).toEqual({
                    x: expected[i].x,
                    y: expected[i].y,
                });
            });
        });
    });

    // openTileの想定動作

    // 1. タイルを開けることができる

    // 2. すでに開かれているタイルの場合、早期リターンする

    // 3. タイルが地雷でない場合、周囲の地雷数が0の場合、周囲のタイルも開ける

    // 4. フラグが立っている場合、フラグを外す

    describe('openTileテスト', () => {
        it('タイル展開テスト', () => {
            const board = createBoard(10, 10);
            const tile = board[5][5];
            const visitedTiles = new Set<string>();

            // タイルを開ける
            openTile(board, tile, visitedTiles);

            // タイルが開かれていることを確認
            expect(board[5][5].isOpen).toBe(true);
        });

        it('すでに開かれているタイルの場合、早期リターンする', () => {
            const board = createBoard(5, 5);
            const tile = board[2][2];
            tile.isOpen = true;
            // 本来フラグは閉じるので、関数が早期リターンすることを確認するためにフラグをたてる。
            tile.isFlag = true;
            const visitedTiles = new Set<string>();
            // すでに開かれているタイルを訪問済みにしておく
            visitedTiles.add(`${tile.x},${tile.y}`);
            console.log(visitedTiles);

            // タイルを開ける
            openTile(board, tile, visitedTiles);

            // タイルが開かれていることを確認
            expect(tile.isFlag).toBe(true);
        });

        it('タイルが地雷でない場合、周囲の地雷数が0の場合、周囲のタイルも開ける', () => {
            const board = createBoard(10, 10);
            // 周囲のタイルも開けるため、地雷を配置
            board[3][3].isMine = true;
            const tile = board[5][5];
            const visitedTiles = new Set<string>();

            // 5,5の周囲の座標をハードコーディング
            const aroundTiles = [
                board[4][4],
                board[4][5],
                board[4][6],
                board[5][4],
                board[5][6],
                board[6][4],
                board[6][5],
                board[6][6],
            ];
            // タイルを開ける
            openTile(board, tile, visitedTiles);

            // タイルが開かれていることを確認
            aroundTiles.forEach((tile) => {
                expect(tile.isOpen).toBe(true);
            });
        });
    });

    it('フラグが立っている場合、フラグを外す', () => {
        const board = createBoard(10, 10);
        const tile = board[5][5];
        tile.isFlag = true;
        const visitedTiles = new Set<string>();

        // タイルを開ける
        openTile(board, tile, visitedTiles);

        // フラグが外れていることを確認
        expect(tile.isFlag).toBe(false);
    });

    describe('toggleFlagテスト', () => {
        it('タイルにフラグを立て外しする', () => {
            const board = createBoard(10, 10);
            const tile = board[5][5];

            // フラグを立てる
            toggleFlag(tile);
            expect(tile.isFlag).toBe(true);

            // フラグを外す
            toggleFlag(tile);
            expect(tile.isFlag).toBe(false);
        });

        it('開いているタイルにはフラグを立て外しできない', () => {
            const board = createBoard(10, 10);
            const tile = board[5][5];
            tile.isOpen = true;

            // フラグを立てる
            toggleFlag(tile);
            expect(tile.isFlag).toBe(false);

            // フラグを外す
            toggleFlag(tile);
            expect(tile.isFlag).toBe(false);
        });
    });
});
