import { describe, expect, it } from 'vitest';
import { createBoard, setMines } from '../domain/mineSweeper';

// ドキュメント setMinesの想定動作

// 1. 初クリック位置とその周囲1マスには地雷を置かない

// 2. 地雷の数が指定した数になるまで地雷を配置する

// エラーハンドリング

// 1. 地雷数がタイル数を超える場合はエラーをスローする

// 2. 初クリック位置がボード外の場合はエラーをスローする

describe('地雷設置テスト', () => {
    it('setMinesテスト', () => {
        const board = createBoard(3, 3);
        const firstClick = { x: 0, y: 0 };
        const numOfMine = 1;

        setMines(board, numOfMine, firstClick);

        let mineCount = 0;

        board.forEach((widthArr) => {
            widthArr.forEach((tile) => {
                if (tile.isMine) mineCount++;
            });
        });

        expect(mineCount).toBe(numOfMine);
    });

    it('周囲一マスに地雷がないか検証', () => {
        const board = createBoard(10, 10);
        const firstClick = { x: 5, y: 5 };
        const numOfMine = 20;

        setMines(board, numOfMine, firstClick);

        const aroundTiles = [
            { x: 4, y: 4 },
            { x: 4, y: 5 },
            { x: 4, y: 6 },
            { x: 5, y: 4 },
            { x: 5, y: 6 },
            { x: 6, y: 4 },
            { x: 6, y: 5 },
            { x: 6, y: 6 },
        ];

        aroundTiles.forEach((tile) => {
            expect(board[tile.y][tile.x].isMine).toBe(false);
        });
    });

    it('地雷数がタイル数を超えるケース', () => {
        const board = createBoard(3, 3);
        const firstClick = { x: 0, y: 0 };
        const numOfMine = 10;

        expect(() => {
            setMines(board, numOfMine, firstClick);
        }).toThrowError('地雷数が多すぎます');
    });

    it('初クリック位置がボード外の場合', () => {
        const board = createBoard(3, 3);
        const firstClick = { x: 10, y: 10 };
        const numOfMine = 1;

        expect(() => {
            setMines(board, numOfMine, firstClick);
        }).toThrowError('初クリック位置が不正です');
    });
});
