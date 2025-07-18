import { describe, expect, it } from 'vitest';
import { createBoard } from '../domain/mineSweeper';

describe('ボードクラスのテスト', () => {
    it('ボードクラス初期化テスト', () => {
        const board = createBoard(10, 5);
        expect(board.length).toBe(5); // height
        expect(board[0].length).toBe(10); // width
        let index = 0;
        board.forEach((widthArr) => {
            widthArr.forEach((tile) => {
                index++;
            });
        });
        expect(index).toBe(50);
        console.log(
            `Board dimensions:H x W ${board.length}x${board[0].length}`,
        );
        board.forEach((row, rowIndex) => {
            console.log(
                `Row ${rowIndex}: ${row.map((tile) => (tile.isMine ? 'M' : 'O')).join(' ')}`,
            );
        });
    });

    it('ボードの初期状態の確認', () => {
        const board = createBoard(3, 3);
        board.forEach((widthArr, yIndex) => {
            widthArr.forEach((tile, xIndex) => {
                expect({ ...tile }).toEqual({
                    x: xIndex,
                    y: yIndex,
                    isMine: false,
                    isOpen: false,
                    isFlag: false,
                    adjacentMines: 0,
                });
            });
        });
    });

    it('ボードサイズが0の場合の動作確認', () => {
        const board = createBoard(0, 0);
        expect(board).toEqual([]);
    });
});
