import { useGameStore } from '@/stores/gameStore';
import { createPinia, setActivePinia } from 'pinia';
import { beforeEach, describe, expect, it } from 'vitest';
import {
    checkGameClear,
    checkGameOver,
    createBoard,
    setMines,
} from '../domain/mineSweeper';

describe('Game Progress', () => {
    describe('checkGameOverテスト', () => {
        it('地雷をクリックしてゲームオーバーになる', () => {
            const board = createBoard(3, 3);

            const clickedTile = board[0][0];
            // 地雷を配置
            clickedTile.isMine = true;
            // 地雷をクリック
            clickedTile.isOpen = true;

            // 地雷をクリック
            const result = checkGameOver(clickedTile);

            expect(result).toBe(true);
        });

        it('地雷以外をクリックしてゲームオーバーにならない', () => {
            const board = createBoard(3, 3);

            const clickedTile = board[0][0];
            // 地雷以外をクリック
            clickedTile.isOpen = true;

            // 地雷以外をクリック
            const result = checkGameOver(clickedTile);

            expect(result).toBe(false);
        });
    });

    describe('checkGameClerテスト', () => {
        it('全ての地雷以外のタイルを開いたらゲームクリア', () => {
            const board = createBoard(3, 3);
            const totalMines = 1;
            const clickedTile = board[0][0];
            setMines(board, totalMines, clickedTile);
            // 地雷以外のタイルを全て開く
            board.forEach((row) => {
                row.forEach((tile) => {
                    if (!tile.isMine) {
                        tile.isOpen = true;
                    }
                });
            });

            const result = checkGameClear(board, totalMines);

            expect(result).toBe(true);
        });

        it('地雷以外のタイルを全て開いていないとき', () => {
            const board = createBoard(3, 3);
            const totalMines = 1;
            const clickedTile = board[0][0];
            setMines(board, totalMines, clickedTile);
            // 地雷以外のタイルを全て開く
            board.forEach((row) => {
                row.forEach((tile) => {
                    if (!tile.isMine) {
                        tile.isOpen = true;
                    }
                });
            });
            // 1つだけ地雷以外のタイルを閉じる
            board[0][1].isOpen = false;

            const result = checkGameClear(board, totalMines);

            expect(result).toBe(false);
        });
    });

    describe('initiarizeテスト', () => {
        beforeEach(() => {
            setActivePinia(createPinia());
        });

        it('初期化テスト', () => {
            const gameStore = useGameStore();
            const width = 10;
            const height = 10;

            gameStore.initiaraize(width, height);

            expect(gameStore.board).toEqual(createBoard(width, height));
            expect(gameStore.width).toBe(width);
            expect(gameStore.height).toBe(height);
            expect(gameStore.visitedTiles.size).toBe(0);
            expect(gameStore.isGameStarted).toBe(false);
            expect(gameStore.isGameOver).toBe(false);
            expect(gameStore.isGameClear).toBe(false);
            expect(gameStore.isFlagMode).toBe(false);
        });

        it('コンティニューでの初期化テスト', () => {
            const gameStore = useGameStore();
            const board = createBoard(10, 10);
            const arrayToVisitedTiles = ['0,0', '1,1'];
            const numOfMines = 20;

            gameStore.continueGame(board, arrayToVisitedTiles, numOfMines);

            expect(gameStore.board).toStrictEqual(board);
            expect(gameStore.width).toBe(board[0].length);
            expect(gameStore.height).toBe(board.length);
            expect(gameStore.numOfMines).toBe(numOfMines);
            expect(gameStore.visitedTiles.size).toBe(
                arrayToVisitedTiles.length,
            );
            expect(gameStore.isGameStarted).toBe(true);
            expect(gameStore.isGameOver).toBe(false);
            expect(gameStore.isGameClear).toBe(false);
            expect(gameStore.isFlagMode).toBe(false);
        });
    });
});
